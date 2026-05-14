<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CompanySetting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@gestao.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Criar role Super Admin
        $superAdmin = Role::create(['name' => 'super-admin']);
        
        // Criar todas as permissões
        $permissions = [
            'view entities', 'create entities', 'edit entities', 'delete entities',
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'view articles', 'create articles', 'edit articles', 'delete articles',
            'view proposals', 'create proposals', 'edit proposals', 'delete proposals',
            'view orders', 'create orders', 'edit orders', 'delete orders',
            'view supplier_orders', 'create supplier_orders', 'edit supplier_orders', 'delete supplier_orders',
            'view financial', 'create financial', 'edit financial', 'delete financial',
            'view calendar', 'create calendar', 'edit calendar', 'delete calendar',
            'view archive', 'create archive', 'edit archive', 'delete archive',
            'view users', 'create users', 'edit users', 'delete users',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'view settings', 'create settings', 'edit settings', 'delete settings',
            'view logs', 'delete logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Atribuir todas as permissões ao Super Admin
        $superAdmin->givePermissionTo(Permission::all());
        
        // Atribuir role ao admin
        $admin->assignRole('super-admin');

        $this->command->info('Usuário Admin criado:');
        $this->command->info('Email: admin@gestao.com');
        $this->command->info('Senha: password123');
    }
}