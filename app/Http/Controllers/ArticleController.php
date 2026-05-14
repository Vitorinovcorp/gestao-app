<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Article::with('vat');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        if ($request->has('low_stock')) {
            $query->whereColumn('stock_current', '<=', 'stock_min');
        }
        
        $articles = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));
        
        return response()->json($articles);
    }
    
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);
        
        $articles = Article::where('is_active', true)
                          ->where(function($q) use ($request) {
                              $q->where('reference', 'like', "%{$request->query}%")
                                ->orWhere('name', 'like', "%{$request->query}%");
                          })
                          ->limit(10)
                          ->get(['id', 'reference', 'name', 'price', 'vat_id']);
        
        return response()->json($articles);
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'reference' => 'required|string|max:50|unique:articles,reference',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'vat_id' => 'required|exists:vat_rates,id',
                'barcode' => 'nullable|string|max:50|unique:articles,barcode',
                'stock_min' => 'nullable|integer|min:0',
                'stock_current' => 'nullable|integer|min:0',
                'observations' => 'nullable|string',
                'is_active' => 'sometimes|boolean'
            ]);
            
            $validated['is_active'] = $request->is_active ?? true;
            $validated['stock_current'] = $request->stock_current ?? 0;
            
            $article = Article::create($validated);
            
            return response()->json([
                'message' => 'Artigo criado com sucesso',
                'article' => $article->load('vat')
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar artigo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show(Article $article)
    {
        $article->load('vat');
        return response()->json($article);
    }
    
    public function update(Request $request, Article $article)
    {
        try {
            $validated = $request->validate([
                'reference' => 'required|string|max:50|unique:articles,reference,' . $article->id,
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'vat_id' => 'required|exists:vat_rates,id',
                'barcode' => 'nullable|string|max:50|unique:articles,barcode,' . $article->id,
                'stock_min' => 'nullable|integer|min:0',
                'stock_current' => 'nullable|integer|min:0',
                'observations' => 'nullable|string',
                'is_active' => 'sometimes|boolean'
            ]);
            
            $article->update($validated);
            
            return response()->json([
                'message' => 'Artigo atualizado com sucesso',
                'article' => $article->load('vat')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar artigo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(Article $article)
    {
        try {
            if ($article->photo_path) {
                Storage::disk('public')->delete($article->photo_path);
            }
            
            $article->delete();
            
            return response()->json([
                'message' => 'Artigo eliminado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao eliminar artigo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function uploadPhoto(Request $request, Article $article)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        
        try {
            if ($article->photo_path) {
                Storage::disk('public')->delete($article->photo_path);
            }
            
            $path = $request->file('photo')->store('articles', 'public');
            $article->update(['photo_path' => $path]);
            
            return response()->json([
                'message' => 'Foto enviada com sucesso',
                'path' => asset('storage/' . $path)
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar foto'], 500);
        }
    }
    
    public function deletePhoto(Article $article)
    {
        try {
            if ($article->photo_path) {
                Storage::disk('public')->delete($article->photo_path);
                $article->update(['photo_path' => null]);
            }
            
            return response()->json(['message' => 'Foto removida com sucesso']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao remover foto'], 500);
        }
    }
    
    public function toggleStatus(Article $article)
    {
        $article->is_active = !$article->is_active;
        $article->save();
        
        $status = $article->is_active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Artigo {$status} com sucesso",
            'is_active' => $article->is_active
        ]);
    }
}