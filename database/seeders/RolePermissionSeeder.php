<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        
        // Create permissions
        $permissions = [
            'view courses', 'enroll in courses', 'complete courses', 
            'get certificates', 'manage profile', 'manage courses', 
            'manage users', 'view analytics', 'create courses',
            'edit courses', 'delete courses', 'approve courses',
            'manage payments', 'manage instructors', 'manage students'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Assign permissions to admin
        $adminRole = Role::findByName('admin', 'web');
        $adminRole->syncPermissions(Permission::all());
        
        // Assign permissions to instructor
        $instructorRole = Role::findByName('instructor', 'web');
        $instructorRole->syncPermissions([
            'view courses', 'manage courses', 'create courses', 
            'edit courses', 'view analytics', 'manage profile'
        ]);
        
        // Assign permissions to student
        $studentRole = Role::findByName('student', 'web');
        $studentRole->syncPermissions([
            'view courses', 'enroll in courses', 'complete courses', 
            'get certificates', 'manage profile'
        ]);
        
        $this->command->info('Roles and permissions seeded successfully!');
    }
}