<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
 app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Use firstOrCreate to prevent "RoleAlreadyExists" errors
        // Create roles if they don't exist
        if (!Role::where('name', 'student')->exists()) {
            Role::create(['name' => 'student', 'guard_name' => 'web']);
        }
        
        if (!Role::where('name', 'instructor')->exists()) {
            Role::create(['name' => 'instructor', 'guard_name' => 'web']);
        }
        
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        // Create permissions safely
        $permissions = [
            'view courses',
            'enroll in courses',
            'complete courses',
            'get certificates',
            'manage profile',
            'manage courses',
            'manage users',
            'view analytics'
        ];

         foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission, 'guard_name' => 'web']);
            }
        }

        // Syncing permissions is often better than givePermissionTo in seeders
        // syncPermissions() replaces existing permissions with the ones provided
        $studentRole = Role::where('name', 'student')->first();
        $instructorRole = Role::where('name', 'instructor')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $studentRole->syncPermissions([
            'view courses', 
            'enroll in courses', 
            'complete courses', 
            'get certificates', 
            'manage profile'
        ]);
        $instructorRole->syncPermissions([
            'view courses', 
            'manage courses', 
            'view analytics', 
            'manage profile'
        ]);

        // Admins get everything
        $adminRole->syncPermissions(Permission::all());

        $studentRole= Role::findByName('student', 'web');
        $studentRole->syncPermissions($permissions);
    }
}