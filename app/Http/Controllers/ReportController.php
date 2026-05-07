<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function adminReports()
    {
        $weeklyData = $this->getWeeklyData();
        $monthlyData = $this->getMonthlyData();
        
        $stats = [
            'total_revenue' => Payment::where('status', 'successful')->sum('amount'),
            'total_enrollments' => Enrollment::count(),
            'total_students' => User::role('student')->count(),
            'total_instructors' => User::role('instructor')->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::where('is_published', true)->count(),
        ];
        
        $topCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($course) {
                return [
                    'title' => $course->title,
                    'enrollments' => $course->enrollments_count,
                    'revenue' => $course->enrollments_count * $course->price,
                ];
            });
        
        return Inertia::render('Admin/Reports/Index', [
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData,
            'stats' => $stats,
            'topCourses' => $topCourses,
        ]);
    }
    
    public function instructorReports()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        $courseIds = $courses->pluck('id');
        
        $stats = [
            'total_students' => Enrollment::whereIn('course_id', $courseIds)->count(),
            'total_revenue' => Enrollment::whereIn('course_id', $courseIds)->sum('amount_paid'),
            'total_courses' => $courses->count(),
            'average_rating' => round($courses->avg('rating') ?? 0, 1),
        ];
        
        $weeklyEnrollments = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyEnrollments[] = [
                'day' => $date->format('D'),
                'count' => Enrollment::whereIn('course_id', $courseIds)
                    ->whereDate('enrolled_at', $date->toDateString())
                    ->count(),
            ];
        }
        
        $courseStats = [];
        foreach ($courses as $course) {
            $enrollments = Enrollment::where('course_id', $course->id);
            $courseStats[] = [
                'title' => $course->title,
                'students' => $enrollments->count(),
                'revenue' => $enrollments->sum('amount_paid'),
                'progress' => round($enrollments->avg('progress_percentage') ?? 0, 1),
            ];
        }
        
        return Inertia::render('Instructor/Reports/Index', [
            'stats' => $stats,
            'weeklyEnrollments' => $weeklyEnrollments,
            'courseStats' => $courseStats,
        ]);
    }
    
    private function getWeeklyData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'day' => $date->format('D'),
                'enrollments' => Enrollment::whereDate('enrolled_at', $date->toDateString())->count(),
                'revenue' => Payment::where('status', 'successful')
                    ->whereDate('paid_at', $date->toDateString())
                    ->sum('amount'),
            ];
        }
        return $data;
    }
    
    private function getMonthlyData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = [
                'month' => $date->format('M'),
                'enrollments' => Enrollment::whereYear('enrolled_at', $date->year)
                    ->whereMonth('enrolled_at', $date->month)
                    ->count(),
                'revenue' => Payment::where('status', 'successful')
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->sum('amount'),
            ];
        }
        return $data;
    }
}