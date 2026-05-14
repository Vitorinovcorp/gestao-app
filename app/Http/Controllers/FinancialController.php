<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use App\Models\Entity;
use App\Models\SupplierOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentProofMail;

class FinancialController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view financial')->only(['supplierInvoices', 'clientBalances', 'bankAccounts']);
        $this->middleware('permission:create financial')->only(['storeSupplierInvoice', 'storeBankAccount']);
        $this->middleware('permission:edit financial')->only(['updateSupplierInvoice', 'updateBankAccount']);
        $this->middleware('permission:delete financial')->only(['deleteSupplierInvoice', 'deleteBankAccount']);
    }
    
    // Supplier Invoices
    public function supplierInvoices(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'supplierOrder']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        
        $invoices = $query->orderBy('invoice_date', 'desc')
                          ->paginate($request->get('per_page', 15));
        
        return response()->json($invoices);
    }
    
    public function storeSupplierInvoice(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:supplier_invoices,number',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'supplier_id' => 'required|exists:entities,id',
            'supplier_order_id' => 'required|exists:supplier_orders,id',
            'total_value' => 'required|numeric|min:0',
            'observations' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            $invoice = SupplierInvoice::create([
                'number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'supplier_id' => $validated['supplier_id'],
                'supplier_order_id' => $validated['supplier_order_id'],
                'total_value' => $validated['total_value'],
                'observations' => $validated['observations'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->withProperties(['total_value' => $invoice->total_value])
                ->log('supplier invoice created');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Fatura de fornecedor criada com sucesso',
                'invoice' => $invoice->load('supplier')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar fatura', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function updateSupplierInvoice(Request $request, SupplierInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Não é possível editar uma fatura paga'], 422);
        }
        
        $validated = $request->validate([
            'invoice_number' => 'sometimes|string|unique:supplier_invoices,number,' . $invoice->id,
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'total_value' => 'sometimes|numeric|min:0',
            'observations' => 'nullable|string'
        ]);
        
        try {
            $invoice->update($validated);
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->log('supplier invoice updated');
            
            return response()->json([
                'message' => 'Fatura atualizada com sucesso',
                'invoice' => $invoice->fresh('supplier')
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar fatura'], 500);
        }
    }
    
    public function markInvoiceAsPaid(Request $request, SupplierInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Fatura já está marcada como paga'], 422);
        }
        
        $validated = $request->validate([
            'send_email' => 'sometimes|boolean',
            'payment_proof' => 'required_if:send_email,true|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        try {
            DB::beginTransaction();
            
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->paid_by = auth()->id();
            
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('payment-proofs', 'private');
                $invoice->payment_proof_path = $path;
            }
            
            $invoice->save();
            
            // Send email if requested
            if ($request->get('send_email', false) && $invoice->supplier->email) {
                Mail::to($invoice->supplier->email)
                    ->send(new PaymentProofMail($invoice));
            }
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->log('supplier invoice marked as paid');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Fatura marcada como paga com sucesso',
                'email_sent' => $request->get('send_email', false)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao marcar fatura como paga', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function uploadInvoiceDocument(Request $request, SupplierInvoice $invoice)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);
        
        try {
            $path = $request->file('document')->store('supplier-invoices', 'private');
            
            $invoice->document_path = $path;
            $invoice->save();
            
            return response()->json([
                'message' => 'Documento enviado com sucesso',
                'path' => $path
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar documento'], 500);
        }
    }
    
    public function downloadInvoiceDocument(SupplierInvoice $invoice)
    {
        if (!$invoice->document_path) {
            return response()->json(['message' => 'Nenhum documento associado a esta fatura'], 404);
        }
        
        return response()->download(storage_path("app/private/{$invoice->document_path}"));
    }
}