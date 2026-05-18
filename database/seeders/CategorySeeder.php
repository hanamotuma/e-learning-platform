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
    Category::create([
    'name' => 'Data Science',
    'slug' => Str::slug('Data Science'),
    ]);
    Category::create([
    'name' => 'Graphic Design',
    'slug' => Str::slug('Graphic Design'),
    ]);
    Category::create([
    'name' => 'Digital Marketing',
    'slug' => Str::slug('Digital Marketing'),
    ]);
    }
}
