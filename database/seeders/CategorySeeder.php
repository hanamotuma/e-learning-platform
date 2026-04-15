<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    
    public function run(): void
    {
        Category::create([

    'name' => 'Web Development',
    'slug' => Str::slug('Web Development'),
    ]);
    }
}
