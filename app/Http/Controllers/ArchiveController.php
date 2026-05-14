<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view archive')->only(['index', 'download', 'search', 'categories']);
        $this->middleware('permission:create archive')->only(['upload']);
        $this->middleware('permission:delete archive')->only(['destroy']);
    }
    
    public function index(Request $request)
    {
        // Placeholder - implementar com model Document
        return response()->json([
            'documents' => [],
            'total' => 0,
            'per_page' => 15,
            'current_page' => 1
        ]);
    }
    
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'entity_id' => 'nullable|integer'
        ]);
        
        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('documents', 'private');
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['file' => $originalName])
                ->log('document uploaded');
            
            return response()->json([
                'message' => 'Documento enviado com sucesso',
                'path' => $path,
                'original_name' => $originalName
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar documento'], 500);
        }
    }
    
    public function download($document)
    {
        // Placeholder - implementar com model Document
        return response()->json(['message' => 'Download não disponível']);
    }
    
    public function destroy($document)
    {
        // Placeholder - implementar com model Document
        return response()->json(['message' => 'Documento eliminado com sucesso']);
    }
    
    public function share(Request $request, $document)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        return response()->json([
            'message' => 'Documento compartilhado com sucesso',
            'share_link' => url("/share/{$document}")
        ]);
    }
    
    public function categories()
    {
        $categories = [
            'faturas' => 'Faturas',
            'contratos' => 'Contratos',
            'propostas' => 'Propostas',
            'encomendas' => 'Encomendas',
            'documentos_cliente' => 'Documentos Cliente',
            'documentos_fornecedor' => 'Documentos Fornecedor',
            'outros' => 'Outros'
        ];
        
        return response()->json($categories);
    }
    
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3'
        ]);
        
        return response()->json([
            'results' => [],
            'total' => 0
        ]);
    }
}