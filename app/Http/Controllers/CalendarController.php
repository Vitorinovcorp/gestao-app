<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\CalendarAction;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    // Eventos
    public function events(Request $request)
    {
        $query = CalendarEvent::with(['type', 'action', 'entity', 'user', 'assignedTo']);
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }
        
        if ($request->has('start')) {
            $query->where('start_datetime', '>=', $request->start);
        }
        
        if ($request->has('end')) {
            $query->where('end_datetime', '<=', $request->end);
        }
        
        $events = $query->get();
        
        return response()->json($events);
    }
    
    public function store(Request $request)
    {
        try {
            $event = CalendarEvent::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'duration_minutes' => $request->duration_minutes,
                'type_id' => $request->type_id,
                'action_id' => $request->action_id,
                'entity_id' => $request->entity_id,
                'user_id' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'status' => $request->status ?? 'scheduled',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Evento criado com sucesso',
                'event' => $event->load(['type', 'action', 'entity'])
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar evento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function showEvent($id)
    {
        $event = CalendarEvent::with(['type', 'action', 'entity', 'user', 'assignedTo'])->findOrFail($id);
        return response()->json($event);
    }
    
    public function updateEvent(Request $request, $id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            $event->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Evento atualizado com sucesso',
                'event' => $event->load(['type', 'action', 'entity'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar evento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroyEvent($id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            $event->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Evento eliminado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar evento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Tipos
    public function types()
    {
        return response()->json(CalendarType::where('is_active', true)->get());
    }
    
    public function storeType(Request $request)
    {
        $type = CalendarType::create($request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string'
        ]));
        
        return response()->json(['success' => true, 'type' => $type]);
    }
    
    public function updateType(Request $request, $id)
    {
        $type = CalendarType::findOrFail($id);
        $type->update($request->all());
        
        return response()->json(['success' => true, 'type' => $type]);
    }
    
    public function deleteType($id)
    {
        $type = CalendarType::findOrFail($id);
        $type->delete();
        
        return response()->json(['success' => true]);
    }
    
    // Ações
    public function actions()
    {
        return response()->json(CalendarAction::where('is_active', true)->get());
    }
    
    public function storeAction(Request $request)
    {
        $action = CalendarAction::create($request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'requires_followup' => 'boolean',
            'default_duration' => 'integer|min:0'
        ]));
        
        return response()->json(['success' => true, 'action' => $action]);
    }
    
    public function updateAction(Request $request, $id)
    {
        $action = CalendarAction::findOrFail($id);
        $action->update($request->all());
        
        return response()->json(['success' => true, 'action' => $action]);
    }
    
    public function deleteAction($id)
    {
        $action = CalendarAction::findOrFail($id);
        $action->delete();
        
        return response()->json(['success' => true]);
    }
}