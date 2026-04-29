<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\LiveSession;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $instructor = Auth::user();
        
        // Get instructor's courses with stats
        $courses = Course::where('instructor_id', $instructor->id)
            ->withCount('enrollments')
            ->withSum('enrollments', 'amount')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'students' => $course->enrollments_count,
                    'progress' => $course->completion_rate ?? 0,
                    'revenue' => $course->enrollments_sum_amount ?? 0,
                    'rating' => $course->average_rating ?? 0,
                    'status' => $course->status,
                    'thumbnail' => $course->thumbnail,
                    'lastUpdated' => $course->updated_at->diffForHumans(),
                ];
            });
        
        // Get recent enrollments for instructor's courses
        $recentEnrollments = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student' => $enrollment->user->name,
                    'course' => $enrollment->course->title,
                    'date' => $enrollment->created_at->format('Y-m-d'),
                    'amount' => $enrollment->amount,
                    'status' => $enrollment->payment_status,
                ];
            });
        
        // Get pending reviews
        $pendingReviews = Review::whereIn('course_id', $courses->pluck('id'))
            ->where('approved', false)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'student' => $review->user->name,
                    'course' => $review->course->title,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'date' => $review->created_at->diffForHumans(),
                ];
            });
        
        // Get upcoming live sessions
        $upcomingLiveSessions = LiveSession::whereIn('course_id', $courses->pluck('id'))
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'course' => $session->course->title,
                    'date' => $session->start_time->format('M d, Y'),
                    'time' => $session->start_time->format('g:i A'),
                    'attendees' => $session->attendees_count ?? 0,
                ];
            });
        
        // Calculate stats
        $stats = [
            'total_students' => Enrollment::whereIn('course_id', $courses->pluck('id'))->count(),
            'total_revenue' => $courses->sum('enrollments_sum_amount'),
            'active_courses' => $courses->where('status', 'published')->count(),
            'avg_rating' => round($courses->avg('average_rating'), 1),
        ];
        
        return inertia('Instructor/Dashboard', [
            'courses' => $courses,
            'recentEnrollments' => $recentEnrollments,
            'pendingReviews' => $pendingReviews,
            'upcomingLiveSessions' => $upcomingLiveSessions,
            'stats' => $stats,
            'auth' => ['user' => $instructor]
        ]);
    }
}