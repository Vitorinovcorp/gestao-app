<?php

namespace App\Http\Controllers;

use App\Models\AISuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AISuggestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        
        Log::info('AISuggestionController@index called', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id
        ]);
        
        // Buscar sugestões do tenant do usuário
        $suggestions = AISuggestion::whereHas('deal', function($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })
        ->where('status', 'pending')
        ->with('deal')
        ->orderBy('suggested_date', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();
        
        Log::info('Suggestions found', ['count' => $suggestions->count()]);
        
        // Se for request AJAX, retorna JSON
        if (request()->wantsJson()) {
            return response()->json([
                'suggestions' => $suggestions,
                'count' => $suggestions->count()
            ]);
        }
        
        // Caso contrário, retorna a view
        return view('ai-suggestions.index', compact('suggestions'));
    }
    
    public function accept($id)
    {
        try {
            $suggestion = AISuggestion::findOrFail($id);
            $user = Auth::user();
            
            // Verificar permissão
            if ($suggestion->deal->tenant_id != $user->tenant_id) {
                abort(403, 'Não autorizado');
            }
            
            $suggestion->update([
                'status' => 'accepted',
                'user_feedback' => 'accepted',
                'feedback_at' => now(),
            ]);
            
            // TODO: Converter em atividade automaticamente
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sugestão aceita']);
            }
            
            return redirect()->route('ai-suggestions.index')
                ->with('success', 'Sugestão aceita e convertida em atividade!');
            
        } catch (\Exception $e) {
            Log::error('Error accepting suggestion: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->route('ai-suggestions.index')
                ->with('error', 'Erro ao aceitar sugestão: ' . $e->getMessage());
        }
    }
    
    public function dismiss($id)
    {
        try {
            $suggestion = AISuggestion::findOrFail($id);
            $user = Auth::user();
            
            if ($suggestion->deal->tenant_id != $user->tenant_id) {
                abort(403);
            }
            
            $suggestion->update([
                'status' => 'dismissed',
                'user_feedback' => 'dismissed',
                'feedback_at' => now(),
            ]);
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sugestão descartada']);
            }
            
            return redirect()->route('ai-suggestions.index')
                ->with('success', 'Sugestão descartada');
                
        } catch (\Exception $e) {
            return redirect()->route('ai-suggestions.index')
                ->with('error', 'Erro ao descartar sugestão');
        }
    }
    
    public function archive($id)
    {
        return $this->dismiss($id);
    }
}