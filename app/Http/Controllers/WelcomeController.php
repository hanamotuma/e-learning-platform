<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Testimonial;
use App\Models\Partner;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Get featured courses (e.g., most popular or recently added)
        $featuredCourses = Course::with('instructor')
            ->where('is_featured', true)
            ->orWhere('is_published', true)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'category' => $course->category?->name ?? $course->category,
                    'price' => $course->price,
                    'rating' => $course->rating ?? 4.5,
                    'reviews' => $course->reviews_count ?? 0,
                    'image' => $course->image ?? $course->thumbnail,
                    'tag' => $course->is_bestseller ? 'Bestseller' : ($course->is_new ? 'New' : 'Popular'),
                    'instructor' => $course->instructor?->name ?? 'Expert Instructor',
                ];
            });
        
        // Get categories with course counts
        $categories = Category::withCount('courses')
            ->orderBy('order_column')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon ?? $this->getCategoryIcon($category->name),
                    'course_count' => $category->courses_count,
                ];
            });
        
        // If no categories in DB, use defaults
        if ($categories->isEmpty()) {
            $categories = collect([
                ['name' => 'Development', 'icon' => '💻'],
                ['name' => 'Design', 'icon' => '🎨'],
                ['name' => 'Business', 'icon' => '📈'],
                ['name' => 'Marketing', 'icon' => '🚀'],
                ['name' => 'AI & Data', 'icon' => '🧠'],
                ['name' => 'Photography', 'icon' => '📷'],
            ]);
        }
        
        // Get partners/trusted companies
        $partners = Partner::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($partner) {
                return [
                    'name' => $partner->name,
                    'logo' => $partner->logo,
                    'url' => $partner->website,
                ];
            });
        
        // If no partners, use defaults
        if ($partners->isEmpty()) {
            $partners = collect([
                ['name' => 'Google'],
                ['name' => 'Meta'],
                ['name' => 'Netflix'],
                ['name' => 'Amazon'],
                ['name' => 'Microsoft'],
                ['name' => 'Adobe'],
            ]);
        }
        
        // Get testimonials
        $testimonials = Testimonial::where('is_approved', true)
            ->orderBy('order')
            ->take(6)
            ->get()
            ->map(function ($testimonial) {
                return [
                    'name' => $testimonial->name,
                    'role' => $testimonial->role,
                    'content' => $testimonial->content,
                    'rating' => $testimonial->rating,
                    'avatar' => $testimonial->avatar,
                ];
            });
        
        // Get stats (could be from settings or calculated)
        $stats = [
            'students' => \App\Models\User::where('role', 'student')->count(),
            'courses' => Course::where('is_published', true)->count(),
            'instructors' => \App\Models\User::where('role', 'instructor')->count(),
            'certificates' => \App\Models\Certificate::count() ?? 50000,
        ];
        
        return inertia('Welcome', [
            'featuredCourses' => $featuredCourses,
            'categories' => $categories,
            'partners' => $partners,
            'testimonials' => $testimonials,
            'stats' => $stats,
            'canLogin' => true,
            'canRegister' => true,
        ]);
    }
    
    private function getCategoryIcon($categoryName)
    {
        $icons = [
            'Development' => '💻',
            'Design' => '🎨',
            'Business' => '📈',
            'Marketing' => '🚀',
            'AI & Data' => '🧠',
            'Photography' => '📷',
            'Music' => '🎵',
            'Health' => '💪',
        ];
        
        return $icons[$categoryName] ?? '📚';
    }
}