<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    protected $courseService;
    public function __construct()
    {
        $this->courseService = new \App\Services\CourseService();
    }
    public function index(Request $request)
    {
        $query = Course::with(['category', 'instructor'])
            ->where('status', 'published');
        
        // Search
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by difficulty
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }
        
        // Sort
        switch ($request->sort) {
            case 'popular':
                $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }
        
        $courses = $query->paginate(12);
        $categories = Category::all();
        
        return inertia('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'difficulty', 'sort']),
        ]);
    }
    
    public function show($id)
    {
        $course = Course::with([
            'category',
            'instructor',
            'sections.lessons',
            'sections.quizzes',
            'reviews.user',
        ])->findOrFail($id);
        
        $isEnrolled = false;
        $progress = null;
        
        if (Auth::check()) {
            $enrollment = Enrollment::where('user_id', Auth::user()->user_id)
                ->where('course_id', $id)
                ->first();
            
            $isEnrolled = $enrollment && $enrollment->status !== 'dropped';
            $progress = $enrollment;
        }
        
        $relatedCourses = Course::with('instructor')
            ->where('category_id', $course->category_id)
            ->where('course_id', '!=', $id)
            ->where('status', 'published')
            ->limit(4)
            ->get();
        
        return inertia('Courses/Show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'progress' => $progress,
            'relatedCourses' => $relatedCourses,
        ]);
    }
    
    public function enroll($id)
    {
        $course = Course::findOrFail($id);
        $user = Auth::user();
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user->user_id)
            ->where('course_id', $id)
            ->first();
        
        if ($existingEnrollment) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }
        
        // Handle payment if course is paid
        if ($course->price > 0) {
            return redirect()->route('payments.checkout', $course);
        }
        
        // Free enrollment
        Enrollment::create([
            'user_id' => $user->user_id,
            'course_id' => $id,
            'status' => 'enrolled',
            'progress_percentage' => 0,
            'enrolled_at' => now(),
        ]);
        
        return redirect()->route('courses.learn', $id)->with('success', 'Successfully enrolled in the course!');
    }
    
    public function learn($id)
    {
        $course = Course::with([
            'sections.lessons',
            'sections.quizzes.questions',
        ])->findOrFail($id);
        
        $enrollment = Enrollment::where('user_id', Auth::user()->user_id)
            ->where('course_id', $id)
            ->firstOrFail();
        
        // Get completed lessons
        $completedLessons = \App\Models\ProgressTracking::where('enrollment_id', $enrollment->enrollment_id)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();
        
        return inertia('Courses/Learn', [
            'course' => $course,
            'enrollment' => $enrollment,
            'completedLessons' => $completedLessons,
        ]);
    }
}