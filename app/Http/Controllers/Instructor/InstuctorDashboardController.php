<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Review;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        $instructor = Auth::user();
        
        // Get instructor's courses
        $courses = Course::with(['category', 'enrollments.user'])
            ->where('instructor_id', $instructor->id)
            ->get();
        
        // Calculate statistics
        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $courses->sum(function($course) {
                return $course->enrollments->count();
            }),
            'total_revenue' => $courses->sum(function($course) {
                return $course->enrollments->sum('amount_paid');
            }),
            'average_rating' => round($courses->avg('rating') ?? 0, 1),
            'total_reviews' => Review::whereIn('course_id', $courses->pluck('id'))->count(),
            'pending_payout' => $courses->sum(function($course) {
                return $course->enrollments->sum('amount_paid') * 0.7; // 70% to instructor
            }),
        ];
        
        // Get recent enrollments
        $recentEnrollments = Enrollment::with(['course', 'user'])
            ->whereIn('course_id', $courses->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get recent reviews
        $recentReviews = Review::with(['course', 'user'])
            ->whereIn('course_id', $courses->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get monthly earnings chart data
        $monthlyEarnings = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyEarnings[] = [
                'month' => $month->format('M'),
                'earnings' => Payment::whereIn('course_id', $courses->pluck('id'))
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }
        
        return Inertia::render('Instructor/Dashboard', [
            'stats' => $stats,
            'courses' => $courses,
            'recentEnrollments' => $recentEnrollments,
            'recentReviews' => $recentReviews,
            'monthlyEarnings' => $monthlyEarnings,
            'auth' => [
                'user' => [
                    'id' => $instructor->id,
                    'name' => $instructor->full_name ?? $instructor->name,
                    'email' => $instructor->email,
                ]
            ]
        ]);
    }
    
    public function courses()
    {
        $courses = Course::with(['category', 'enrollments'])
            ->where('instructor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Instructor/Courses', [
            'courses' => $courses,
        ]);
    }
    
    public function students()
    {
        $courses = Course::where('instructor_id', Auth::id())->pluck('id');
        
        $students = User::whereHas('enrollments', function($query) use ($courses) {
            $query->whereIn('course_id', $courses);
        })->with(['enrollments' => function($query) use ($courses) {
            $query->whereIn('course_id', $courses);
        }])->paginate(20);
        
        return Inertia::render('Instructor/Students', [
            'students' => $students,
        ]);
    }
    
    public function earnings()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        
        $totalEarnings = $courses->sum(function($course) {
            return $course->enrollments->sum('amount_paid');
        });
        
        $pendingPayout = $totalEarnings * 0.3; // 30% platform fee
        $availableBalance = $totalEarnings * 0.7;
        
        $transactions = Payment::with(['course', 'user'])
            ->whereIn('course_id', $courses->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return Inertia::render('Instructor/Earnings', [
            'totalEarnings' => $totalEarnings,
            'pendingPayout' => $pendingPayout,
            'availableBalance' => $availableBalance,
            'transactions' => $transactions,
        ]);
    }
}