<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // User management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Hazard reports
            'view_hazards',
            'create_hazards',
            'edit_hazards',
            'delete_hazards',
            
            // Bird entries
            'view_birds',
            'create_birds',
            'edit_birds',
            'delete_birds',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles if they don't exist and sync permissions
        $roles = [
            'Super Admin' => $permissions,
            'Admin' => [
                'view_users',
                'view_hazards',
                'create_hazards',
                'edit_hazards',
                'view_birds',
                'create_birds',
                'edit_birds',
            ],
            'User' => [
                'view_hazards',
                'create_hazards',
                'view_birds',
                'create_birds',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Create super admin user if it doesn't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'designation' => 'System Administrator',
                'department' => 'IT',
                'airport' => 'HDQ',
                'contact_number' => '1234567890'
            ]
        );

        // Assign super admin role
        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
        }
    }
}