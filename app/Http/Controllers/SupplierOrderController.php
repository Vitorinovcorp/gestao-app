<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use Illuminate\Http\Request;

class SupplierOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierOrder::with(['supplier']);
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));
        
        return response()->json($orders);
    }
    
    public function store(Request $request)
    {
        // Implementar depois
        return response()->json(['message' => 'Em desenvolvimento']);
    }
    
    public function show($id)
    {
        $order = SupplierOrder::with(['supplier', 'lines.article'])->findOrFail($id);
        return response()->json($order);
    }
    
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Em desenvolvimento']);
    }
    
    public function destroy($id)
    {
        return response()->json(['message' => 'Em desenvolvimento']);
    }
    
    public function close($id)
    {
        return response()->json(['message' => 'Em desenvolvimento']);
    }
    
    public function downloadPdf($id)
    {
        return response()->json(['message' => 'Em desenvolvimento']);
    }
}