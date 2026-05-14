<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $permissions = [
            // Entities
            'view entities', 'create entities', 'edit entities', 'delete entities',
            
            // Contacts
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            
            // Articles
            'view articles', 'create articles', 'edit articles', 'delete articles',
            
            // Proposals
            'view proposals', 'create proposals', 'edit proposals', 'delete proposals',
            
            // Orders
            'view orders', 'create orders', 'edit orders', 'delete orders',
            
            // Supplier Orders
            'view supplier_orders', 'create supplier_orders', 'edit supplier_orders', 'delete supplier_orders',
            
            // Financial
            'view financial', 'create financial', 'edit financial', 'delete financial',
            
            // Calendar
            'view calendar', 'create calendar', 'edit calendar', 'delete calendar',
            
            // Archive
            'view archive', 'create archive', 'edit archive', 'delete archive',
            
            // Users
            'view users', 'create users', 'edit users', 'delete users',
            
            // Permissions
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            
            // Settings
            'view settings', 'create settings', 'edit settings', 'delete settings',
            
            // Logs
            'view logs', 'delete logs',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create Super Admin role if it doesn't exist
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        
        // Assign all permissions to Super Admin
        if ($superAdmin->permissions()->count() === 0) {
            $superAdmin->givePermissionTo(Permission::all());
        }
    }
}