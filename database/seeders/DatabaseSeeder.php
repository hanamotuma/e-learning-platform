<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
      
        $roles = ['admin', 'instructor', 'student'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'System Admin',
                'email' => 'admin@edumind.com',
                'password' => Hash::make('password'),
                'is_active' => 1,
            ]
        );
        $admin->assignRole('admin');

        
        $instructor = User::firstOrCreate(
            ['username' => 'instructor'],
            [
                'full_name' => 'Hana Instructor',
                'email' => 'instructor@edumind.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $instructor = User::first() ?? User::factory()->create([
    'username' => 'instructor1',
    'full_name' => 'Hana Motuma',
    
]);
Course::create([
    'title' => 'Complete Web Development Bootcamp',
    'description' => 'Learn full-stack development',
    'price' => 49.99,
    'difficulty_level' => 'beginner',
    'instructor_id' => $instructor->id, // This was likely missing or null
    'category_id' => 1,
    'status' => 'published',
]);

        
        $student = User::firstOrCreate(
            ['username' => 'student'],
            [
                'full_name' => 'Jane Student',
                'email' => 'student@edumind.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $student->assignRole('student');

        $categories = [
            'Web Development',
            'Mobile Development',
            'Data Science',
            'UI/UX Design',
            'Digital Marketing',
            'Business',
            'Photography',
            'Music Production',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category]);
        }

       
        $courses = [
            [
                'title' => 'Complete Web Development Bootcamp',
                'description' => 'Learn full-stack development',
                'price' => 49.99,
                'difficulty_level' => 'beginner',
            ],
            [
                'title' => 'Advanced React Patterns',
                'description' => 'Master advanced React concepts',
                'price' => 79.99,
                'difficulty_level' => 'advanced',
            ],
        ];

        foreach ($courses as $courseData) {
            Course::firstOrCreate(
                ['title' => $courseData['title']],
                array_merge($courseData, [
                    'instructor_id' => $instructor->user_id,
                    'category_id' => 1,
                    'status' => 'published',
                ])
            );
        }

        
    }
}