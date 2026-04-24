<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            [
                'email' => 'admin@edumind.com',
            ],
            [
                'name' => 'System Admin',
                'email' => 'admin@edumind.com',
                'password' => Hash::make('password123'),
            ]
        );
    }
}