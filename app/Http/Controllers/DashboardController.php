<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        $enrolledCourses = Enrollment::with(['course.category', 'course.instructor'])
            ->where('user_id', $user->user_id)
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
            ->where('status', 'published')
            ->whereNotIn('course_id', $enrolledCourses->pluck('course_id'))
            ->limit(6)
            ->get();
        
        return inertia('Dashboard/StudentDashboard', [
            'stats' => $stats,
            'enrolledCourses' => $recentCourses,
            'recommendedCourses' => $recommendedCourses,
        ]);
    }

    private function instructorDashboard()
    {
        $user = Auth::user();
        
        $courses = Course::with(['category', 'enrollments'])
            ->where('instructor_id', $user->user_id)
            ->get();
        
        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $courses->sum(function($course) {
                return $course->enrollments->where('status', '!=', 'dropped')->count();
            }),
            'total_revenue' => $courses->sum(function($course) {
                return $course->enrollments->where('status', 'completed')->count() * $course->price;
            }),
            'average_rating' => round($courses->avg('average_rating') ?? 0, 1),
        ];
        
        return inertia('Dashboard/InstructorDashboard', [
            'stats' => $stats,
            'courses' => $courses,
        ]);
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_courses' => Course::count(),
            'total_revenue' => \App\Models\Payment::where('status', 'successful')->sum('amount'),
            'total_enrollments' => Enrollment::count(),
        ];
        
        $recentUsers = \App\Models\User::latest()->limit(10)->get();
        $recentCourses = Course::with('instructor')->latest()->limit(10)->get();
        
        return inertia('Dashboard/AdminDashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
        ]);
    }
}