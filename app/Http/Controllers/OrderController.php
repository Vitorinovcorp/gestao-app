<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Proposal;
use App\Models\Entity;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client', 'createdBy', 'lines.article']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $year = date('Y');
            $lastOrder = Order::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $lastNumber = $lastOrder ? intval(substr($lastOrder->number, -4)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $number = "E{$year}{$newNumber}";

            $order = Order::create([
                'number' => $number,
                'order_date' => now(),
                'client_id' => $request->client_id,
                'status' => 'rascunho',
                'created_by' => auth()->id(),
                'total_value' => 0,
                'notes' => $request->notes,
                'expected_delivery' => $request->expected_delivery
            ]);

            $totalValue = 0;

            foreach ($request->lines as $lineData) {
                $article = Article::find($lineData['article_id']);
                $vatRate = $article->vat->rate ?? 23;
                $quantity = $lineData['quantity'];
                $unitPrice = $lineData['unit_price'];
                $subtotal = $quantity * $unitPrice;
                $vatAmount = $subtotal * ($vatRate / 100);
                $lineTotal = $subtotal + $vatAmount;

                OrderLine::create([
                    'order_id' => $order->id,
                    'article_id' => $lineData['article_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'vat_rate' => $vatRate,
                    'line_subtotal' => $subtotal,
                    'line_vat' => $vatAmount,
                    'line_total' => $lineTotal
                ]);

                $totalValue += $lineTotal;

                // Atualizar stock
                $article->decrement('stock_current', $quantity);
            }

            $order->total_value = $totalValue;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encomenda criada com sucesso',
                'order' => $order->load(['client', 'lines.article'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar encomenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== 'draft') {
                return response()->json(['message' => 'Não é possível editar uma encomenda já confirmada'], 422);
            }

            DB::beginTransaction();

            // Restaurar stock antigo
            foreach ($order->lines as $line) {
                $article = Article::find($line->article_id);
                if ($article) {
                    $article->increment('stock_current', $line->quantity);
                }
            }

            // Deletar linhas antigas
            OrderLine::where('order_id', $id)->delete();

            $totalValue = 0;
            foreach ($request->lines as $lineData) {
                $line = OrderLine::create([
                    'order_id' => $order->id,
                    'article_id' => $lineData['article_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price']
                ]);

                $totalValue += $lineData['quantity'] * $lineData['unit_price'];

                // Atualizar novo stock
                $article = Article::find($lineData['article_id']);
                if ($article) {
                    $article->decrement('stock_current', $lineData['quantity']);
                }
            }

            $order->update([
                'client_id' => $request->client_id,
                'notes' => $request->notes,
                'expected_delivery' => $request->expected_delivery,
                'total_value' => $totalValue
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encomenda atualizada com sucesso',
                'order' => $order->load(['client', 'lines.article'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar encomenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== 'draft') {
                return response()->json(['message' => 'Não é possível eliminar uma encomenda já confirmada'], 422);
            }

            // Restaurar stock
            foreach ($order->lines as $line) {
                $article = Article::find($line->article_id);
                if ($article) {
                    $article->increment('stock_current', $line->quantity);
                }
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Encomenda eliminada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar encomenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function close($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->status = 'confirmed';
            $order->confirmed_at = now();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Encomenda confirmada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar encomenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertToSupplierOrders($id)
    {
        // TODO: Implementar conversão para encomendas de fornecedor
        return response()->json([
            'success' => true,
            'message' => 'Funcionalidade em desenvolvimento'
        ]);
    }

    public function downloadPdf($id)
    {
        try {
            $order = Order::with(['client', 'lines.article', 'createdBy'])->findOrFail($id);

            $pdf = Pdf::loadView('pdfs.encomenda', ['encomenda' => $order]);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download("encomenda_{$order->number}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addLine(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            $line = OrderLine::create([
                'order_id' => $order->id,
                'article_id' => $request->article_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price
            ]);

            // Atualizar stock
            $article = Article::find($request->article_id);
            if ($article) {
                $article->decrement('stock_current', $request->quantity);
            }

            // Recalcular total
            $totalValue = $order->lines()->sum(DB::raw('quantity * unit_price'));
            $order->total_value = $totalValue;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Linha adicionada com sucesso',
                'line' => $line->load('article')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar linha: ' . $e->getMessage()
            ], 500);
        }
    }
}
