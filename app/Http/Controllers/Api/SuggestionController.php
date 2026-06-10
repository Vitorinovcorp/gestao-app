<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AISuggestion;
use App\Models\DealActivity;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuggestionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $suggestions = AISuggestion::whereHas('deal', function($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id);
        })
        ->where('status', 'pending')
        ->with('deal')
        ->orderBy('suggested_date', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();
        
        return response()->json([
            'suggestions' => $suggestions,
            'total' => $suggestions->count()
        ]);
    }
    
    public function accept($id)
    {
        $suggestion = AISuggestion::with('deal')->findOrFail($id);
        
        // Converter a sugestão em atividade
        $activity = DealActivity::create([
            'deal_id' => $suggestion->deal_id,
            'user_id' => auth()->id(),
            'type' => $suggestion->suggested_action_type ?? 'task',
            'description' => $suggestion->description,
            'created_at' => now(),
        ]);
        
        // Se tiver data sugerida, criar evento no calendário
        if ($suggestion->suggested_date) {
            $event = CalendarEvent::create([
                'title' => $suggestion->title,
                'description' => $suggestion->description,
                'start_datetime' => $suggestion->suggested_date . ' 10:00:00',
                'user_id' => auth()->id(),
                'deal_id' => $suggestion->deal_id,
                'tenant_id' => auth()->user()->tenant_id,
            ]);
        }
        
        // Atualizar sugestão
        $suggestion->update([
            'status' => 'completed',
            'user_feedback' => 'accepted',
            'feedback_at' => now(),
            'converted_to_activity_at' => now(),
            'converted_activity_id' => $activity->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Sugestão convertida em atividade com sucesso!',
            'activity' => $activity,
            'event' => $event ?? null
        ]);
    }
    
    public function dismiss($id)
    {
        $suggestion = AISuggestion::findOrFail($id);
        
        $suggestion->update([
            'status' => 'dismissed',
            'user_feedback' => 'dismissed',
            'feedback_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Sugestão arquivada'
        ]);
    }
    
    public function postpone($id, Request $request)
    {
        $suggestion = AISuggestion::findOrFail($id);
        
        $newDate = $request->input('date', now()->addDays(3)->format('Y-m-d'));
        
        $suggestion->update([
            'suggested_date' => $newDate,
            'user_feedback' => 'ignored',
            'feedback_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Sugestão adiada para {$newDate}"
        ]);
    }
    
    public function stats()
    {
        $user = auth()->user();
        
        $stats = [
            'total' => AISuggestion::whereHas('deal', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })->count(),
            
            'pending' => AISuggestion::whereHas('deal', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })->where('status', 'pending')->count(),
            
            'completed' => AISuggestion::whereHas('deal', function($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })->where('status', 'completed')->count(),
            
            'acceptance_rate' => $this->calculateAcceptanceRate($user->tenant_id),
        ];
        
        return response()->json($stats);
    }
    
    protected function calculateAcceptanceRate($tenantId)
    {
        $total = AISuggestion::whereHas('deal', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->whereNotNull('user_feedback')->count();
        
        if ($total === 0) return 0;
        
        $accepted = AISuggestion::whereHas('deal', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->where('user_feedback', 'accepted')->count();
        
        return round(($accepted / $total) * 100, 2);
    }
}