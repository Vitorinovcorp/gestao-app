<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefone' => $user->telefone,
                    'grupo_permissoes' => $user->grupo_permissoes,
                    'status' => $user->status ?? ($user->is_active ? 'active' : 'inactive') 
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'telefone' => 'nullable|string|max:20',
            'grupo_permissoes' => 'required|string',
            'status' => 'required|in:active,inactive',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'grupo_permissoes' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name'
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return response()->json([
            'message' => 'Utilizador atualizado com sucesso',
            'user' => $user->load('roles')
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Não pode eliminar o próprio utilizador'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Utilizador eliminado com sucesso']);
    }

    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $status = $user->status === 'active' ? 'ativado' : 'desativado';

        return response()->json([
            'message' => "Utilizador {$status} com sucesso",
            'status' => $user->status
        ]);
    }

    public function reset2FA(User $user)
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json(['message' => '2FA redefinido com sucesso']);
    }

    public function sendWelcomeEmail(User $user)
    {
        
        return response()->json(['message' => 'Email de boas-vindas enviado']);
    }
}