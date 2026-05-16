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
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::with('users', 'permissions')->get();
        return view('permissions.index', compact('roles'));
    }

    public function getRoles()
    {
        $roles = Role::with('users', 'permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name]);
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Grupo criado com sucesso', 'role' => $role->load('permissions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $request->name]);
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Grupo atualizado com sucesso', 'role' => $role->load('permissions')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return response()->json(['success' => false, 'message' => 'Não é possível eliminar o grupo Super Admin'], 422);
        }
        try {
            $role->delete();
            return response()->json(['success' => true, 'message' => 'Grupo eliminado com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function permissionsList()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function allPermissions()
    {
        $modules = [
            'entidades' => ['ver', 'criar', 'editar', 'eliminar'],
            'contactos' => ['ver', 'criar', 'editar', 'eliminar'],
            'artigos' => ['ver', 'criar', 'editar', 'eliminar'],
            'propostas' => ['ver', 'criar', 'editar', 'eliminar'],
            'encomendas' => ['ver', 'criar', 'editar', 'eliminar'],
            'encomendas_fornecedor' => ['ver', 'criar', 'editar', 'eliminar'],
            'financeiro' => ['ver', 'criar', 'editar', 'eliminar'],
            'calendario' => ['ver', 'criar', 'editar', 'eliminar'],
            'arquivo' => ['ver', 'criar', 'editar', 'eliminar'],
            'utilizadores' => ['ver', 'criar', 'editar', 'eliminar'],
            'permissoes' => ['ver', 'criar', 'editar', 'eliminar'],
            'configuracoes' => ['ver', 'criar', 'editar', 'eliminar'],
            'logs' => ['ver', 'eliminar']
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

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return response()->json($role);
    }
}
