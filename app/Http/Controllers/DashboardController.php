<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('instructor')) {
            return $this->instructorDashboard();
        } else {
            return $this->studentDashboard();
        }
    }

    private function studentDashboard()
    {
        $user = Auth::user();
        $user->refresh();
        
        $enrolledCourses = Enrollment::with(['course.category', 'course.instructor'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'dropped')
            ->get();
        
        $stats = [
            'total_courses' => $enrolledCourses->count(),
            'completed_courses' => $enrolledCourses->where('status', 'completed')->count(),
            'in_progress_courses' => $enrolledCourses->where('status', 'in_progress')->count(),
            'average_progress' => round($enrolledCourses->avg('progress_percentage') ?? 0, 1),
        ];
        
        $recentCourses = $enrolledCourses->take(4);
        
        $recommendedCourses = Course::with(['category', 'instructor'])
            ->where('is_published', true)
            ->whereNotIn('id', $enrolledCourses->pluck('course_id'))
            ->limit(6)
            ->get();
        
        return Inertia::render('Student/Dashboard', [
            'stats' => $stats,
            'enrolledCourses' => $recentCourses,
            'recommendedCourses' => $recommendedCourses,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->full_name,
                    'email' => $user->email,
                ]
            ]
        ]);
    }

    private function instructorDashboard()
    {
        $user = Auth::user();
        
        $courses = Course::with(['category', 'enrollments'])
            ->where('instructor_id', $user->id)
            ->get();
        
        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $courses->sum(function($course) {
                return $course->enrollments->where('status', '!=', 'dropped')->count();
            }),
            'total_revenue' => $courses->sum(function($course) {
                return $course->enrollments->where('status', 'completed')->count() * $course->price;
            }),
            'average_rating' => round($courses->avg('rating') ?? 0, 1),
        ];
        
        return Inertia::render('Instructor/Dashboard', [
            'stats' => $stats,
            'courses' => $courses,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->full_name,
                    'email' => $user->email,
                ]
            ]
        ]);
    }

    private function adminDashboard()
    {
        $user = Auth::user();
        
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_courses' => Course::count(),
            'total_revenue' => \App\Models\Payment::where('status', 'successful')->sum('amount'),
            'total_enrollments' => Enrollment::count(),
        ];
        
        $recentUsers = \App\Models\User::latest()->limit(10)->get();
        $recentCourses = Course::with('instructor')->latest()->limit(10)->get();
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->full_name,
                    'email' => $user->email,
                ]
            ]
        ]);
    }
}