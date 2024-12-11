<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Services\ResourcePermissionService;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get all permissions from resources
        $permissionService = new ResourcePermissionService();
        $permissions = $permissionService->getResourcePermissions();

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and their permissions
        $roles = [
            'Super Admin' => $permissions, // Super Admin gets all permissions
            'Admin' => array_filter($permissions, function($permission) {
                // Admins can't manage roles and some sensitive operations
                return !Str::contains($permission, [
                    'roles',
                    'force_delete',
                    'restore',
                ]);
            }),
            'User' => array_filter($permissions, function($permission) {
                // Users can only view and create certain resources
                return Str::startsWith($permission, ['view_', 'create_']) 
                    && !Str::contains($permission, [
                        'roles',
                        'permissions',
                        'users',
                    ]);
            }),
        ];

        // Create roles and assign permissions
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