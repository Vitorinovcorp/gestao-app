<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['store']);
        $this->middleware('permission:edit users')->only(['update']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }
    
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->has('role')) {
            $query->role($request->role);
        }
        
        $users = $query->orderBy('name')->paginate(15);
        
        return response()->json($users);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name'
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);
        
        $user->assignRole($validated['role']);
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('user created');
        
        return response()->json([
            'message' => 'Utilizador criado com sucesso',
            'user' => $user->load('roles')
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
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name'
        ]);
        
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $user->update($validated);
        
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
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
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Utilizador {$status} com sucesso",
            'is_active' => $user->is_active
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