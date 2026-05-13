<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the home page with featured courses
     */
    public function index()
    {
        $user = Auth::user();
        // Get featured courses (published and limited to 12)
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
                    'badge' => $this->getBadgeForCourse($course),
                    'level' => $course->difficulty_level ?? 'Beginner to Advanced',
                    'slug' => $course->slug,
                    'inCart' => false,
                ];
            });

        // If no courses in database, use sample data
        if ($featuredCourses->isEmpty()) {
            $featuredCourses = $this->getSampleCourses();
        }

        // Get categories for filter
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $this->getCategoryIcon($category->name),
            ];
        });

        // Add "All" category
        $allCategories = collect([
            ['id' => 'all', 'name' => 'All', 'icon' => 'BookOpen'],
        ])->concat($categories);

        // Get cart count from session or localStorage (will be handled by frontend)
        // For backend, we just pass auth user info

        return Inertia::render('Home', [
            'featuredCourses' => $featuredCourses,
            'categories' => $allCategories,
            'auth' => [
                'user' => Auth::check() ? [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->full_name ?? Auth::user()->name,
                    'email' => Auth::user()->email,
                    'username' => Auth::user()->username,
                ] : null,
            ],
            'canLogin' => true,
            'canRegister' => true,
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

    /**
     * Get badge for course based on criteria
     */
    private function getBadgeForCourse($course)
    {
        if ($course->students_count > 100000) {
            return 'Bestseller';
        }
        if ($course->rating >= 4.8) {
            return 'Top Rated';
        }
        if ($course->is_published && $course->created_at->diffInDays(now()) < 30) {
            return 'New';
        }
        return 'Featured';
    }

    /**
     * Get icon for category based on name
     */
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

    /**
     * Get sample courses for when database is empty
     */
    private function getSampleCourses()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'The Complete Web Development Bootcamp 2026',
                'instructor' => 'Dr. Angela Yu',
                'price' => 4990,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 12450,
                'students' => 125000,
                'hours' => 45,
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
                'category' => 'development',
                'badge' => 'Bestseller',
                'level' => 'Beginner to Advanced',
                'slug' => 'complete-web-development-bootcamp',
                'inCart' => false,
            ],
            [
                'id' => 2,
                'title' => 'Advanced UI/UX Design Masterclass',
                'instructor' => 'Sarah Johnson',
                'price' => 3990,
                'originalPrice' => 14990,
                'rating' => 4.9,
                'reviews' => 8450,
                'students' => 45000,
                'hours' => 32,
                'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=500',
                'category' => 'design',
                'badge' => 'Top Rated',
                'level' => 'Intermediate',
                'slug' => 'advanced-ui-ux-design',
                'inCart' => false,
            ],
            [
                'id' => 3,
                'title' => 'Artificial Intelligence A-Z 2026',
                'instructor' => 'Prof. Andrew Ng',
                'price' => 5990,
                'originalPrice' => 24990,
                'rating' => 4.9,
                'reviews' => 15600,
                'students' => 89000,
                'hours' => 52,
                'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=500',
                'category' => 'data',
                'badge' => 'Trending',
                'level' => 'All Levels',
                'slug' => 'ai-ml-masterclass',
                'inCart' => false,
            ],
            [
                'id' => 4,
                'title' => 'Digital Marketing & Growth Hacking',
                'instructor' => 'Gary Vee',
                'price' => 3490,
                'originalPrice' => 12990,
                'rating' => 4.7,
                'reviews' => 9820,
                'students' => 67000,
                'hours' => 28,
                'image' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8f2c293?w=500',
                'category' => 'marketing',
                'badge' => 'Popular',
                'level' => 'Beginner',
                'slug' => 'digital-marketing-growth',
                'inCart' => false,
            ],
            [
                'id' => 5,
                'title' => 'Python for Data Science & Machine Learning',
                'instructor' => 'Jose Portilla',
                'price' => 5490,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 18700,
                'students' => 112000,
                'hours' => 38,
                'image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=500',
                'category' => 'data',
                'badge' => 'Best for Beginners',
                'level' => 'Beginner',
                'slug' => 'python-data-science',
                'inCart' => false,
            ],
            [
                'id' => 6,
                'title' => 'React 19 - The Complete Guide (2026 Edition)',
                'instructor' => 'Maximilian Schwarzmüller',
                'price' => 4490,
                'originalPrice' => 16990,
                'rating' => 4.9,
                'reviews' => 15600,
                'students' => 95000,
                'hours' => 42,
                'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=500',
                'category' => 'development',
                'badge' => 'Trending',
                'level' => 'Intermediate',
                'slug' => 'react-19-guide',
                'inCart' => false,
            ],
        ]);
    }

    /**
     * Search courses (AJAX endpoint)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $courses = Course::with(['category', 'instructor'])
            ->where('is_published', true)
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor?->name,
                    'price' => $course->price,
                    'image' => $course->image,
                   
                ];
            });
        
        return response()->json($courses);
    }
}