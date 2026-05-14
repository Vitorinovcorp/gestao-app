<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\CalendarAction;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view calendar')->only(['events', 'types', 'actions']);
        $this->middleware('permission:create calendar')->only(['store', 'storeType', 'storeAction']);
        $this->middleware('permission:edit calendar')->only(['update', 'updateType', 'updateAction']);
        $this->middleware('permission:delete calendar')->only(['destroy', 'deleteType', 'deleteAction']);
    }
    
    // ==================== EVENTOS ====================
    public function events(Request $request)
    {
        $query = CalendarEvent::with(['type', 'action', 'entity', 'user', 'assignedTo']);
        
        if ($request->has('start')) {
            $query->where('start_datetime', '>=', $request->start);
        }
        
        if ($request->has('end')) {
            $query->where('end_datetime', '<=', $request->end);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }
        
        $events = $query->get();
        
        return response()->json($events);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'type_id' => 'required|exists:calendar_types,id',
            'action_id' => 'required|exists:calendar_actions,id',
            'entity_id' => 'nullable|exists:entities,id',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string',
            'is_all_day' => 'boolean',
            'reminders' => 'nullable|array'
        ]);
        
        $validated['user_id'] = auth()->id();
        $validated['duration_minutes'] = $this->calculateDuration($validated['start_datetime'], $validated['end_datetime']);
        $validated['status'] = 'scheduled';
        
        $event = CalendarEvent::create($validated);
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($event)
            ->log('calendar event created');
        
        return response()->json([
            'message' => 'Evento criado com sucesso',
            'event' => $event->load(['type', 'action'])
        ], 201);
    }
    
    public function show(CalendarEvent $event)
    {
        $event->load(['type', 'action', 'entity', 'user', 'assignedTo']);
        return response()->json($event);
    }
    
    public function update(Request $request, CalendarEvent $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'sometimes|date',
            'end_datetime' => 'sometimes|date|after:start_datetime',
            'type_id' => 'sometimes|exists:calendar_types,id',
            'action_id' => 'sometimes|exists:calendar_actions,id',
            'entity_id' => 'nullable|exists:entities,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'location' => 'nullable|string',
            'is_all_day' => 'boolean'
        ]);
        
        if (isset($validated['start_datetime']) && isset($validated['end_datetime'])) {
            $validated['duration_minutes'] = $this->calculateDuration($validated['start_datetime'], $validated['end_datetime']);
        }
        
        $event->update($validated);
        
        return response()->json([
            'message' => 'Evento atualizado com sucesso',
            'event' => $event
        ]);
    }
    
    public function destroy(CalendarEvent $event)
    {
        $event->delete();
        return response()->json(['message' => 'Evento eliminado com sucesso']);
    }
    
    // ==================== TIPOS ====================
    public function types()
    {
        $types = CalendarType::where('is_active', true)->get();
        return response()->json($types);
    }
    
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string'
        ]);
        
        $type = CalendarType::create($validated);
        
        return response()->json([
            'message' => 'Tipo criado com sucesso',
            'type' => $type
        ], 201);
    }
    
    public function updateType(Request $request, CalendarType $type)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'color' => 'sometimes|string|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $type->update($validated);
        
        return response()->json([
            'message' => 'Tipo atualizado com sucesso',
            'type' => $type
        ]);
    }
    
    public function deleteType(CalendarType $type)
    {
        $type->delete();
        return response()->json(['message' => 'Tipo eliminado com sucesso']);
    }
    
    // ==================== AÇÕES ====================
    public function actions()
    {
        $actions = CalendarAction::where('is_active', true)->get();
        return response()->json($actions);
    }
    
    public function storeAction(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'requires_followup' => 'boolean',
            'default_duration' => 'integer|min:0'
        ]);
        
        $action = CalendarAction::create($validated);
        
        return response()->json([
            'message' => 'Ação criada com sucesso',
            'action' => $action
        ], 201);
    }
    
    public function updateAction(Request $request, CalendarAction $action)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'requires_followup' => 'boolean',
            'default_duration' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);
        
        $action->update($validated);
        
        return response()->json([
            'message' => 'Ação atualizada com sucesso',
            'action' => $action
        ]);
    }
    
    public function deleteAction(CalendarAction $action)
    {
        $action->delete();
        return response()->json(['message' => 'Ação eliminada com sucesso']);
    }
    
    private function calculateDuration($start, $end)
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        $interval = $startDate->diff($endDate);
        return $interval->h * 60 + $interval->i;
    }
}