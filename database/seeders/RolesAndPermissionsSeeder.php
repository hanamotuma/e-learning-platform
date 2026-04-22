<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions using firstOrCreate to avoid "Already Exists" errors
        $viewCourses = Permission::firstOrCreate(['name' => 'view courses']);
        Permission::firstOrCreate(['name' => 'create courses']);
        Permission::firstOrCreate(['name' => 'edit courses']);

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $student = Role::firstOrCreate(['name' => 'student']);

        // Assign permissions
        // Syncing is better than givePermissionTo in seeders because it prevents duplicates
        $admin->syncPermissions(Permission::all());
        $student->syncPermissions($viewCourses);
    }
}