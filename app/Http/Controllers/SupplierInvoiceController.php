<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use App\Models\Entity;
use App\Models\SupplierOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentProofMail;

class SupplierInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'supplierOrder']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        $invoices = $query->orderBy('invoice_date', 'desc')
                          ->paginate($request->get('per_page', 15));
        
        return response()->json($invoices);
    }
    
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'number' => 'required|string|unique:supplier_invoices,number',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'supplier_id' => 'required|exists:entities,id',
                'supplier_order_id' => 'nullable|exists:supplier_orders,id',
                'total_value' => 'required|numeric|min:0',
                'observations' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
            ]);
            
            $invoice = new SupplierInvoice();
            $invoice->number = $validated['number'];
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->supplier_id = $validated['supplier_id'];
            $invoice->supplier_order_id = $validated['supplier_order_id'] ?? null;
            $invoice->total_value = $validated['total_value'];
            $invoice->subtotal = $validated['total_value'];
            $invoice->vat_total = 0;
            $invoice->observations = $validated['observations'] ?? null;
            $invoice->status = 'pending';
            $invoice->created_by = auth()->id();
            
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('supplier-invoices', 'private');
                $invoice->document_path = $path;
            }
            
            $invoice->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fatura criada com sucesso',
                'invoice' => $invoice->load(['supplier', 'supplierOrder'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar fatura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $invoice = SupplierInvoice::with(['supplier', 'supplierOrder'])->findOrFail($id);
        return response()->json($invoice);
    }
    
    public function update(Request $request, $id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);
            
            if ($invoice->status === 'paid') {
                return response()->json(['message' => 'Não é possível editar uma fatura paga'], 422);
            }
            
            $validated = $request->validate([
                'number' => 'required|string|unique:supplier_invoices,number,' . $id,
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'supplier_id' => 'required|exists:entities,id',
                'supplier_order_id' => 'nullable|exists:supplier_orders,id',
                'total_value' => 'required|numeric|min:0',
                'observations' => 'nullable|string'
            ]);
            
            $invoice->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Fatura atualizada com sucesso',
                'invoice' => $invoice->load(['supplier', 'supplierOrder'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar fatura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);
            
            if ($invoice->document_path) {
                Storage::disk('private')->delete($invoice->document_path);
            }
            if ($invoice->payment_proof_path) {
                Storage::disk('private')->delete($invoice->payment_proof_path);
            }
            
            $invoice->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Fatura eliminada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar fatura: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function markAsPaid(Request $request, $id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);
            
            if ($invoice->status === 'paid') {
                return response()->json(['message' => 'Fatura já está paga'], 422);
            }
            
            $validated = $request->validate([
                'send_email' => 'boolean',
                'payment_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
            ]);
            
            DB::beginTransaction();
            
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->paid_by = auth()->id();
            
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('payment-proofs', 'private');
                $invoice->payment_proof_path = $path;
            }
            
            $invoice->save();
            
            // Enviar email se solicitado
            $emailSent = false;
            if ($request->send_email && $invoice->supplier->email) {
                Mail::to($invoice->supplier->email)->send(new PaymentProofMail($invoice));
                $emailSent = true;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fatura marcada como paga',
                'email_sent' => $emailSent
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function downloadDocument($id)
    {
        $invoice = SupplierInvoice::findOrFail($id);
        
        if (!$invoice->document_path) {
            return response()->json(['message' => 'Documento não encontrado'], 404);
        }
        
        return Storage::disk('private')->download($invoice->document_path, "fatura_{$invoice->number}.pdf");
    }
    
    public function downloadPaymentProof($id)
    {
        $invoice = SupplierInvoice::findOrFail($id);
        
        if (!$invoice->payment_proof_path) {
            return response()->json(['message' => 'Comprovativo não encontrado'], 404);
        }
        
        return Storage::disk('private')->download($invoice->payment_proof_path, "comprovativo_{$invoice->number}.pdf");
    }
}