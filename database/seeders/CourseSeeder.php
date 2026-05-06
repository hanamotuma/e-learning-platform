<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // 1. Get an instructor (or create one if none exist)
        $instructor = User::first() ?? User::factory()->create(['role' => 'instructor']);

        $courses = [
            [
                'title' => 'Full-Stack Web Development 2026',
                'slug' => 'full-stack-web-development-2026',
                'description' => 'Master the entire stack from database to deployment with modern frameworks.',
                'price' => 89.99,
                'category_id' => 1,
                'instructor_bio' => 'Senior Software Engineer with 15+ years of experience',
                'rating' => 4.9,
                'reviews_count' => 2400,
                'image_url' => 'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg',
                'level' => 'Intermediate',
                'duration_hours' => 45,
                'curriculum' => json_encode([
                    ['title' => 'Introduction', 'lessons' => ['Welcome to the Course', 'Setting Up Environment']],
                    ['title' => 'Frontend Development', 'lessons' => ['HTML/CSS Basics', 'JavaScript Fundamentals', 'Vue.js Framework']],
                ]),
                'requirements' => json_encode(['Basic computer knowledge', 'Willingness to learn']),
                'learning_outcomes' => json_encode(['Build full-stack applications', 'Deploy to production']),
                'is_published' => true,
            ],
            [
                'title' => 'Advanced UI/UX Design Systems',
                'slug' => 'advanced-ui-ux-design-systems',
                'description' => 'Build scalable design systems for modern enterprise applications.',
                'price' => 74.99,
                'instructor_bio' => 'Lead Product Designer at Fortune 500 company',
                'rating' => 4.8,
                'reviews_count' => 1800,
                'image_url' => 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg',
                'level' => 'Advanced',
                'duration_hours' => 38,
                'is_published' => true,
            ],
            [
                'title' => 'AI & Machine Learning Masterclass',
                'slug' => 'ai-machine-learning-masterclass',
                'description' => 'Dive deep into neural networks and predictive modeling.',
                'price' => 99.00,
                'instructor_bio' => 'AI Researcher and Professor',
                'rating' => 5.0,
                'reviews_count' => 950,
                'image_url' => 'https://images.pexels.com/photos/373543/pexels-photo-373543.jpeg',
                'level' => 'Intermediate',
                'duration_hours' => 52,
                'is_published' => true,
            ],
            [
                'title' => 'Mastering Startup Strategy',
                'slug' => 'mastering-startup-strategy',
                'description' => 'The blueprint for scaling your vision from idea to successful exit.',
                'price' => 59.99,
                'instructor_bio' => 'Serial Entrepreneur & Venture Capitalist',
                'rating' => 4.7,
                'reviews_count' => 1200,
                'image_url' => 'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg',
                'level' => 'All Levels',
                'duration_hours' => 30,
                'is_published' => true,
            ],
            [
                'title' => 'Digital Growth Hacking 101',
                'slug' => 'digital-growth-hacking-101',
                'description' => 'Unconventional strategies to explode your user base and revenue.',
                'price' => 45.00,
                'instructor_bio' => 'Marketing Expert & Growth Specialist',
                'rating' => 4.6,
                'reviews_count' => 3100,
                'image_url' => 'https://images.pexels.com/photos/905163/pexels-photo-905163.jpeg',
                'level' => 'Beginner',
                'duration_hours' => 25,
                'is_published' => true,
            ],
            [
                'title' => 'Motion Graphics with After Effects',
                'slug' => 'motion-graphics-after-effects',
                'description' => 'Animate like a pro using industry-standard motion design tools.',
                'price' => 65.00,
                'instructor_bio' => 'Award-winning Motion Designer',
                'rating' => 4.9,
                'reviews_count' => 820,
                'image_url' => 'https://images.pexels.com/photos/251225/pexels-photo-251225.jpeg',
                'level' => 'Intermediate',
                'duration_hours' => 40,
                'is_published' => true,
            ],
        ];

        foreach ($courses as $data) {
            Course::create([
                'title'            => $data['title'],
                'slug'             => $data['slug'],
                'description'      => $data['description'],
                'price'            => $data['price'],
                'instructor_id'    => $instructor->id, // Fixed: Applied to every course
                'category_id'      => $data['category_id'] ?? 1, // Fixed: Ensures category exists
                'difficulty_level' => $data['level'], // Fixed: Maps 'level' to 'difficulty_level'
                'status'           => 'published', // Fixed: Added required column
                'instructor_bio'   => $data['instructor_bio'],
                'rating'           => $data['rating'],
                'reviews_count'    => $data['reviews_count'],
                'image_url'        => $data['image_url'],
                'duration_hours'   => $data['duration_hours'],
                'is_published'     => $data['is_published'],
                'curriculum'       => $data['curriculum'] ?? null,
                'requirements'     => $data['requirements'] ?? null,
                'learning_outcomes' => $data['learning_outcomes'] ?? null,
            ]);
        }
    }
}