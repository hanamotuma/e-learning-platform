<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Clean out existing records to start completely fresh
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('categories')->truncate();
        DB::table('courses')->truncate();

        // 2. Create Spatie Roles
        $roles = ['admin', 'instructor', 'student'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 3. Insert Admin using direct DB Query to bypass model constraints
        $adminId = DB::table('users')->insertGetId([
            'username' => 'admin',
            'full_name' => 'Hana Motuma',
            'email' => 'hanimtnm@gmail.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $admin = \App\Models\User::find($adminId);
        $admin->assignRole('admin');

        // 4. Insert Instructor using direct DB Query
        $instructorId = DB::table('users')->insertGetId([
            'username' => 'instructor',
            'full_name' => 'Hana Instructor',
            'email' => 'instructor@edumind.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $instructor = \App\Models\User::find($instructorId);
        $instructor->assignRole('instructor');

        // 5. Create Categories
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
            DB::table('categories')->insert([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Create Courses using the guaranteed $instructorId
        DB::table('courses')->insert([
            'title' => 'Complete Web Development Bootcamp',
            'slug' => Str::slug('Complete Web Development Bootcamp'),
            'description' => 'Learn full-stack development',
            'price' => 49.99,
            'original_price' => 69.99,
            'hours' => 40,
            'level' => 'beginner',
            'difficulty_level' => 'beginner',
            'date' => now(),
            'instructor_id' => $instructorId, // Guaranteed valid integer ID
            'category_id' => 1,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'title' => 'Advanced React Patterns',
            'slug' => Str::slug('Advanced React Patterns'),
            'description' => 'Master advanced React concepts',
            'price' => 79.99,
            'original_price' => 99.99,
            'hours' => 20,
            'level' => 'advanced',
            'difficulty_level' => 'advanced',
            'date' => now(),
            'instructor_id' => $instructorId, // Guaranteed valid integer ID
            'category_id' => 1,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
            'phone' => null,
            'bio' => null,
        ]);

        // 7. Insert Student User
        $studentId = DB::table('users')->insertGetId([
            'username' => 'student',
            'full_name' => 'Jane Student',
            'email' => 'student@edumind.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $student = \App\Models\User::find($studentId);
        $student->assignRole('student');

        // 8. Run external seeders if they exist
        try {
            $this->call(CourseSeeder::class);
            $this->call(CategorySeeder::class);
        } catch (\Exception $e) {
            // Skips optional seeders if they cause separate issues
        }

        Schema::enableForeignKeyConstraints();
    }
}