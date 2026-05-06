<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's enrollments with course details
        $enrollments = $user->enrollments()
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
        
        // Calculate statistics
        $totalCourses = $user->enrollments()->count();
        $completedCourses = $user->enrollments()->where('completed', true)->count();
        $totalProgress = $user->enrollments()->avg('progress') ?? 0;
        
        // Get recent activity (last 30 days)
        $recentEnrollments = $user->enrollments()
            ->with('course')
            ->where('enrolled_at', '>=', now()->subDays(30))
            ->count();
        
        // Get certificates earned
        $certificates = $user->enrollments()
            ->where('completed', true)
            ->with('course')
            ->get();
        
        return Inertia::render('Student/Dashboard', [
            'enrollments' => $enrollments,
            'totalCourses' => $totalCourses,
            'completedCourses' => $completedCourses,
            'totalProgress' => round($totalProgress),
            'recentEnrollments' => $recentEnrollments,
            'certificates' => $certificates,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'joined_date' => $user->created_at->format('F Y')
            ]
        ]);
    }
}