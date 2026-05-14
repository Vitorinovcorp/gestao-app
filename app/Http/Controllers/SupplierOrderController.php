<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use App\Models\SupplierOrderLine;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supplier_orders')->only(['index', 'show']);
        $this->middleware('permission:create supplier_orders')->only(['store']);
        $this->middleware('permission:edit supplier_orders')->only(['update']);
        $this->middleware('permission:delete supplier_orders')->only(['destroy']);
    }
    
    public function index(Request $request)
    {
        $query = SupplierOrder::with(['supplier', 'createdBy', 'lines.article']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        $orders = $query->orderBy('order_date', 'desc')->paginate(15);
        
        return response()->json($orders);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:entities,id',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.article_id' => 'required|exists:articles,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            $order = new SupplierOrder();
            $order->number = $this->generateSupplierOrderNumber();
            $order->order_date = now();
            $order->supplier_id = $validated['supplier_id'];
            $order->status = 'draft';
            $order->expected_delivery = $validated['expected_delivery'] ?? null;
            $order->notes = $validated['notes'] ?? null;
            $order->created_by = auth()->id();
            $order->total_value = 0;
            $order->save();
            
            $totalValue = 0;
            
            foreach ($validated['lines'] as $lineData) {
                $line = new SupplierOrderLine();
                $line->supplier_order_id = $order->id;
                $line->article_id = $lineData['article_id'];
                $line->quantity = $lineData['quantity'];
                $line->unit_price = $lineData['unit_price'];
                $line->save();
                
                $totalValue += $lineData['quantity'] * $lineData['unit_price'];
            }
            
            $order->total_value = $totalValue;
            $order->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Encomenda de fornecedor criada com sucesso',
                'order' => $order->load(['supplier', 'lines.article'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar encomenda'], 500);
        }
    }
    
    public function show(SupplierOrder $supplierOrder)
    {
        $supplierOrder->load(['supplier', 'createdBy', 'lines.article']);
        return response()->json($supplierOrder);
    }
    
    public function update(Request $request, SupplierOrder $supplierOrder)
    {
        $validated = $request->validate([
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);
        
        $supplierOrder->update($validated);
        
        return response()->json([
            'message' => 'Encomenda atualizada com sucesso',
            'order' => $supplierOrder
        ]);
    }
    
    public function destroy(SupplierOrder $supplierOrder)
    {
        $supplierOrder->delete();
        return response()->json(['message' => 'Encomenda eliminada com sucesso']);
    }
    
    public function close(SupplierOrder $supplierOrder)
    {
        $supplierOrder->status = 'sent';
        $supplierOrder->ordered_at = now();
        $supplierOrder->save();
        
        return response()->json(['message' => 'Encomenda enviada ao fornecedor']);
    }
    
    public function downloadPdf(SupplierOrder $supplierOrder)
    {
        $pdf = Pdf::loadView('pdfs.supplier-order', ['order' => $supplierOrder]);
        return $pdf->download("encomenda_fornecedor_{$supplierOrder->number}.pdf");
    }
    
    public function addLine(Request $request, SupplierOrder $supplierOrder)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0'
        ]);
        
        $line = new SupplierOrderLine($validated);
        $line->supplier_order_id = $supplierOrder->id;
        $line->save();
        
        $totalValue = $supplierOrder->lines()->sum(DB::raw('quantity * unit_price'));
        $supplierOrder->total_value = $totalValue;
        $supplierOrder->save();
        
        return response()->json([
            'message' => 'Linha adicionada com sucesso',
            'line' => $line->load('article')
        ], 201);
    }
    
    public function updateLine(Request $request, SupplierOrder $supplierOrder, SupplierOrderLine $line)
    {
        $line->update($request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0'
        ]));
        
        $totalValue = $supplierOrder->lines()->sum(DB::raw('quantity * unit_price'));
        $supplierOrder->total_value = $totalValue;
        $supplierOrder->save();
        
        return response()->json(['message' => 'Linha atualizada']);
    }
    
    public function deleteLine(SupplierOrder $supplierOrder, SupplierOrderLine $line)
    {
        $line->delete();
        
        $totalValue = $supplierOrder->lines()->sum(DB::raw('quantity * unit_price'));
        $supplierOrder->total_value = $totalValue;
        $supplierOrder->save();
        
        return response()->json(['message' => 'Linha eliminada']);
    }
    
    public function generateNumber()
    {
        return response()->json(['number' => $this->generateSupplierOrderNumber()]);
    }
    
    private function generateSupplierOrderNumber()
    {
        $year = date('Y');
        $lastOrder = SupplierOrder::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastOrder ? intval(substr($lastOrder->number, -4)) + 1 : 1;
        return "F{$year}" . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}