<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view permissions')->only(['index', 'showGroup']);
        $this->middleware('permission:create permissions')->only(['storeGroup']);
        $this->middleware('permission:edit permissions')->only(['updateGroup', 'syncPermissions']);
        $this->middleware('permission:delete permissions')->only(['deleteGroup']);
    }
    
    public function index(Request $request)
    {
        $groups = Role::with('users')
                      ->withCount('users')
                      ->orderBy('name')
                      ->paginate($request->get('per_page', 15));
        
        return response()->json($groups);
    }
    
    public function allPermissions()
    {
        $modules = [
            'entities' => ['view', 'create', 'edit', 'delete'],
            'contacts' => ['view', 'create', 'edit', 'delete'],
            'articles' => ['view', 'create', 'edit', 'delete'],
            'proposals' => ['view', 'create', 'edit', 'delete'],
            'orders' => ['view', 'create', 'edit', 'delete'],
            'supplier_orders' => ['view', 'create', 'edit', 'delete'],
            'financial' => ['view', 'create', 'edit', 'delete'],
            'calendar' => ['view', 'create', 'edit', 'delete'],
            'archive' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'permissions' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'create', 'edit', 'delete'],
            'logs' => ['view']
        ];
        
        $allPermissions = [];
        
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$module}";
                $allPermissions[] = [
                    'name' => $permissionName,
                    'module' => $module,
                    'action' => $action
                ];
            }
        }
        
        return response()->json($allPermissions);
    }
    
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);
        
        try {
            DB::beginTransaction();
            
            $role = Role::create(['name' => $validated['name']]);
            
            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->log('permission group created');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Grupo de permissões criado com sucesso',
                'group' => $role->load('permissions')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar grupo', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function showGroup(Role $group)
    {
        $group->load(['permissions', 'users']);
        
        return response()->json([
            'group' => $group,
            'permissions' => $group->permissions,
            'users' => $group->users
        ]);
    }
    
    public function updateGroup(Request $request, Role $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $group->id . '|max:255'
        ]);
        
        if ($group->name === 'super-admin') {
            return response()->json(['message' => 'Não é possível modificar o grupo Super Admin'], 422);
        }
        
        try {
            $group->update(['name' => $validated['name']]);
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($group)
                ->log('permission group updated');
            
            return response()->json([
                'message' => 'Grupo atualizado com sucesso',
                'group' => $group
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar grupo'], 500);
        }
    }
    
    public function syncPermissions(Request $request, Role $group)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);
        
        if ($group->name === 'super-admin') {
            return response()->json(['message' => 'Não é possível modificar permissões do grupo Super Admin'], 422);
        }
        
        try {
            $group->syncPermissions($validated['permissions']);
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($group)
                ->withProperties(['permissions' => $validated['permissions']])
                ->log('permissions synchronized');
            
            return response()->json([
                'message' => 'Permissões sincronizadas com sucesso',
                'permissions' => $group->permissions
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao sincronizar permissões'], 500);
        }
    }
    
    public function deleteGroup(Role $group)
    {
        if ($group->name === 'super-admin') {
            return response()->json(['message' => 'Não é possível eliminar o grupo Super Admin'], 422);
        }
        
        if ($group->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível eliminar este grupo pois possui utilizadores associados'
            ], 422);
        }
        
        try {
            $group->delete();
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($group)
                ->log('permission group deleted');
            
            return response()->json(['message' => 'Grupo eliminado com sucesso']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao eliminar grupo'], 500);
        }
    }
    
    public function checkPermission(Request $request)
    {
        $request->validate([
            'permission' => 'required|string'
        ]);
        
        $hasPermission = auth()->user()->hasPermissionTo($request->permission);
        
        return response()->json(['has_permission' => $hasPermission]);
    }
}