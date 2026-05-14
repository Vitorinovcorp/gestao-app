<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        $query = Entity::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nif', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('type') && in_array($request->type, ['client', 'supplier', 'both'])) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $entities = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));
        
        return response()->json($entities);
    }
    
    public function clients(Request $request)
    {
        $query = Entity::whereIn('type', ['client', 'both']);
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nif', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->orderBy('name')->paginate(15));
    }
    
    public function suppliers(Request $request)
    {
        $query = Entity::whereIn('type', ['supplier', 'both']);
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nif', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->orderBy('name')->paginate(15));
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => ['required', 'in:client,supplier,both'],
                'nif' => 'required|string|unique:entities,nif|max:20',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'email' => 'nullable|email|max:255',
                'is_active' => 'boolean'
            ]);
            
            // Generate sequential number
            $lastEntity = Entity::orderBy('id', 'desc')->first();
            $number = $lastEntity ? intval($lastEntity->number) + 1 : 1;
            $validated['number'] = str_pad($number, 6, '0', STR_PAD_LEFT);
            
            $entity = Entity::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Entidade criada com sucesso',
                'entity' => $entity
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar entidade: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $entity = Entity::findOrFail($id);
        return response()->json($entity);
    }
    
    public function update(Request $request, $id)
    {
        try {
            $entity = Entity::findOrFail($id);
            
            $validated = $request->validate([
                'type' => ['required', Rule::in(['client', 'supplier', 'both'])],
                'nif' => 'required|string|max:20|unique:entities,nif,' . $id,
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'email' => 'nullable|email|max:255',
                'is_active' => 'boolean'
            ]);
            
            $entity->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Entidade atualizada com sucesso',
                'entity' => $entity
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar entidade: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $entity = Entity::findOrFail($id);
            $entity->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Entidade eliminada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar entidade: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleStatus($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->is_active = !$entity->is_active;
        $entity->save();
        
        $status = $entity->is_active ? 'ativada' : 'desativada';
        
        return response()->json([
            'success' => true,
            'message' => "Entidade {$status} com sucesso",
            'is_active' => $entity->is_active
        ]);
    }
    
    public function validateNif(Request $request)
    {
        $exists = Entity::where('nif', $request->nif)->exists();
        
        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'NIF já existe na base de dados' : 'NIF disponível'
        ]);
    }
    
    public function viesCheck(Request $request)
    {
        // Implementação simples sem dependências externas
        return response()->json([
            'valid' => true,
            'name' => $request->nif,
            'address' => 'Endereço não disponível'
        ]);
    }
}