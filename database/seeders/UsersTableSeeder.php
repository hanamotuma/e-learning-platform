<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'full_name' => 'System Admin',
            'email' => 'admin@edumind.com',
            'password' => Hash::make('password123'), // Use a simple password for testing
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}