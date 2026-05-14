<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalLine;
use App\Models\Entity;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with(['client', 'createdBy', 'lines.article']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        $proposals = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));
        
        return response()->json($proposals);
    }
    
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $year = date('Y');
            $lastProposal = Proposal::whereYear('created_at', $year)
                                    ->orderBy('id', 'desc')
                                    ->first();
            $lastNumber = $lastProposal ? intval(substr($lastProposal->number, -4)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $number = "P{$year}{$newNumber}";
            
            $proposal = Proposal::create([
                'number' => $number,
                'proposal_date' => now(),
                'client_id' => $request->client_id,
                'validity' => now()->addDays($request->validity_days ?? 30),
                'status' => 'draft',
                'created_by' => auth()->id(),
                'total_value' => 0,
                'notes' => $request->notes
            ]);
            
            $totalValue = 0;
            
            foreach ($request->lines as $lineData) {
                // Buscar o artigo para pegar a taxa de IVA
                $article = Article::find($lineData['article_id']);
                $vatRate = $article->vat ? $article->vat->rate : 23;
                
                $line = ProposalLine::create([
                    'proposal_id' => $proposal->id,
                    'article_id' => $lineData['article_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $vatRate
                ]);
                
                $totalValue += $lineData['quantity'] * $lineData['unit_price'];
            }
            
            $proposal->total_value = $totalValue;
            $proposal->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Proposta criada com sucesso',
                'proposal' => $proposal->load(['client', 'lines.article'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar proposta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $proposal = Proposal::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return response()->json($proposal);
    }
    
    public function update(Request $request, $id)
    {
        try {
            $proposal = Proposal::findOrFail($id);
            
            if ($proposal->status !== 'draft') {
                return response()->json(['message' => 'Não é possível editar uma proposta fechada'], 422);
            }
            
            DB::beginTransaction();
            
            $proposal->update([
                'client_id' => $request->client_id,
                'validity' => $request->validity,
                'notes' => $request->notes
            ]);
            
            // Deletar linhas antigas
            ProposalLine::where('proposal_id', $id)->delete();
            
            $totalValue = 0;
            foreach ($request->lines as $lineData) {
                // Buscar o artigo para pegar a taxa de IVA
                $article = Article::find($lineData['article_id']);
                $vatRate = $article->vat ? $article->vat->rate : 23;
                
                $line = ProposalLine::create([
                    'proposal_id' => $proposal->id,
                    'article_id' => $lineData['article_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $vatRate
                ]);
                
                $totalValue += $lineData['quantity'] * $lineData['unit_price'];
            }
            
            $proposal->total_value = $totalValue;
            $proposal->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Proposta atualizada com sucesso',
                'proposal' => $proposal->load(['client', 'lines.article'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar proposta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $proposal = Proposal::findOrFail($id);
            
            if ($proposal->status !== 'draft') {
                return response()->json(['message' => 'Não é possível eliminar uma proposta fechada'], 422);
            }
            
            $proposal->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Proposta eliminada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar proposta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function close($id)
    {
        try {
            $proposal = Proposal::findOrFail($id);
            $proposal->status = 'closed';
            $proposal->proposal_date = now();
            $proposal->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Proposta fechada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fechar proposta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function convertToOrder($id)
    {
        try {
            $proposal = Proposal::with('lines')->findOrFail($id);
            
            if ($proposal->status !== 'closed') {
                return response()->json(['message' => 'A proposta precisa estar fechada'], 422);
            }
            
            // Criar encomenda a partir da proposta
            $year = date('Y');
            $lastOrder = \App\Models\Order::whereYear('created_at', $year)
                                          ->orderBy('id', 'desc')
                                          ->first();
            $lastNumber = $lastOrder ? intval(substr($lastOrder->number, -4)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $orderNumber = "E{$year}{$newNumber}";
            
            $order = \App\Models\Order::create([
                'number' => $orderNumber,
                'order_date' => now(),
                'client_id' => $proposal->client_id,
                'status' => 'rascunho',
                'created_by' => auth()->id(),
                'total_value' => $proposal->total_value,
                'notes' => 'Encomenda gerada a partir da proposta ' . $proposal->number,
                'proposal_id' => $proposal->id
            ]);
            
            foreach ($proposal->lines as $line) {
                \App\Models\OrderLine::create([
                    'order_id' => $order->id,
                    'article_id' => $line->article_id,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'vat_rate' => $line->vat_rate,
                    'line_subtotal' => $line->quantity * $line->unit_price,
                    'line_vat' => ($line->quantity * $line->unit_price) * ($line->vat_rate / 100),
                    'line_total' => ($line->quantity * $line->unit_price) * (1 + $line->vat_rate / 100)
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Encomenda criada com sucesso',
                'order_id' => $order->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao converter: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function downloadPdf($id)
    {
        try {
            $proposta = Proposal::with(['client', 'lines.article', 'createdBy'])->findOrFail($id);
            
            $pdf = Pdf::loadView('pdfs.proposta', ['proposta' => $proposta]);
            $pdf->setPaper('a4', 'portrait');
            
            return $pdf->download("proposta_{$proposta->number}.pdf");
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}