<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hasTelefone = Schema::hasColumn('users', 'telefone');
        $hasGrupoPermissoes = Schema::hasColumn('users', 'grupo_permissoes');
        $hasStatus = Schema::hasColumn('users', 'status');
        
        $adminData = [
            'name' => 'Administrador',
            'email' => 'admin@exemplo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
        
        if ($hasTelefone) $adminData['telefone'] = '912345678';
        if ($hasGrupoPermissoes) $adminData['grupo_permissoes'] = 'admin';
        if ($hasStatus) $adminData['status'] = 'active';
        
        User::updateOrCreate(
            ['email' => 'admin@exemplo.com'],
            $adminData
        );

        $gestorData = [
            'name' => 'Gestor Sistema',
            'email' => 'gestor@exemplo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
        
        if ($hasTelefone) $gestorData['telefone'] = '923456789';
        if ($hasGrupoPermissoes) $gestorData['grupo_permissoes'] = 'gestor';
        if ($hasStatus) $gestorData['status'] = 'active';
        
        User::updateOrCreate(
            ['email' => 'gestor@exemplo.com'],
            $gestorData
        );

        $operadorData = [
            'name' => 'Operador',
            'email' => 'operador@exemplo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
        
        if ($hasTelefone) $operadorData['telefone'] = '934567890';
        if ($hasGrupoPermissoes) $operadorData['grupo_permissoes'] = 'operador';
        if ($hasStatus) $operadorData['status'] = 'active';
        
        User::updateOrCreate(
            ['email' => 'operador@exemplo.com'],
            $operadorData
        );

        $visualizadorData = [
            'name' => 'Visualizador',
            'email' => 'visualizador@exemplo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
        
        if ($hasTelefone) $visualizadorData['telefone'] = '945678901';
        if ($hasGrupoPermissoes) $visualizadorData['grupo_permissoes'] = 'visualizador';
        if ($hasStatus) $visualizadorData['status'] = 'inactive';
        
        User::updateOrCreate(
            ['email' => 'visualizador@exemplo.com'],
            $visualizadorData
        );
    }
}