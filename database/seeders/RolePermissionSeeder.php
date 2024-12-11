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
        // Create permissions
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_hazard_reports',
            'create_hazard_reports',
            'edit_hazard_reports',
            'delete_hazard_reports',
            'view_bird_entries',
            'create_bird_entries',
            'edit_bird_entries',
            'delete_bird_entries',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view_users',
            'view_roles',
            'view_hazard_reports',
            'create_hazard_reports',
            'edit_hazard_reports',
            'view_bird_entries',
            'create_bird_entries',
            'edit_bird_entries',
        ]);

        $user = Role::create(['name' => 'User']);
        $user->givePermissionTo([
            'view_hazard_reports',
            'create_hazard_reports',
            'view_bird_entries',
            'create_bird_entries',
        ]);

        // Create a super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $superAdminUser->assignRole('Super Admin');
    }
}