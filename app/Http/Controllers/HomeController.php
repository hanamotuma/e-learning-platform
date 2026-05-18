<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // If user is logged in, redirect to student dashboard
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect based on role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('instructor')) {
                return redirect()->route('instructor.dashboard');
            }
            return redirect()->route('student.dashboard');
        }
        
        // For guests, show the welcome page
        $featuredCourses = Course::with(['category', 'instructor'])
            ->where('is_published', true)
            ->latest()
            ->take(12)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor?->name ?? 'Expert Instructor',
                    'price' => (float) $course->price,
                    'originalPrice' => (float) ($course->price * 3),
                    'rating' => $course->rating ?? 4.8,
                    'reviews' => $course->reviews_count ?? rand(1000, 20000),
                    'students' => $course->students_count ?? rand(10000, 150000),
                    'hours' => $course->duration_hours ?? rand(20, 60),
                    'image' => $course->image ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
                    'category' => $course->category?->name ?? 'Development',
                    'badge' => 'Featured',
                    'level' => $course->difficulty_level ?? 'Beginner to Advanced',
                    'slug' => $course->slug,
                    'inCart' => false,
                ];
            });
        
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $this->getCategoryIcon($category->name),
            ];
        });
        
        $allCategories = collect([
            ['id' => 'all', 'name' => 'All', 'icon' => 'BookOpen'],
        ])->concat($categories);
        
        return Inertia::render('Home', [
            'featuredCourses' => $featuredCourses,
            'categories' => $allCategories,
            'stats' => [
                ['value' => '50M+', 'label' => 'Learners'],
                ['value' => '5,000+', 'label' => 'Courses'],
                ['value' => '100+', 'label' => 'Countries'],
                ['value' => '85%', 'label' => 'Career Growth'],
            ],
            'benefits' => [
                ['icon' => 'Globe', 'title' => 'Global Community', 'description' => 'Join 50M+ learners worldwide'],
                ['icon' => 'Shield', 'title' => 'Lifetime Access', 'description' => 'Learn at your own pace'],
                ['icon' => 'Headphones', 'title' => '24/7 Support', 'description' => 'Get help when you need it'],
                ['icon' => 'Award', 'title' => 'Certificates', 'description' => 'Shareable credentials'],
            ],
        ]);
    }
    
    private function getCategoryIcon($categoryName)
    {
        $icons = [
            'Development' => 'Code',
            'Design' => 'Palette',
            'Business' => 'Briefcase',
            'Marketing' => 'Megaphone',
            'Data Science' => 'Database',
            'Data' => 'Database',
            'IT & Software' => 'Code',
            'Personal Development' => 'User',
        ];
        return $icons[$categoryName] ?? 'BookOpen';
    }
}