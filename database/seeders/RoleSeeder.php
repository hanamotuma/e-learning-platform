<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
{
    // Create the student role
    Role::create(['name' => 'student']);
    
    // You can also add your other roles here
    Role::create(['name' => 'instructor']);
    Role::create(['name' => 'admin']);
}
}