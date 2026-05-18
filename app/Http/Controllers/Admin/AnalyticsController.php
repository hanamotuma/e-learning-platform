<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Analytics/Index');
    }

    public function getData()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalStudents = User::role('student')->count();
        $totalCourses = Course::count();
        $totalInstructors = User::role('instructor')->count();

        // Monthly revenue for chart
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Payment::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ];
        }

        // Top courses by enrollment
        $topCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'enrollments' => $course->enrollments_count,
                    'revenue' => $course->enrollments_count * $course->price
                ];
            });

        // Enrollment trend
        $enrollmentTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $enrollments = Enrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $enrollmentTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $enrollments
            ];
        }

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_students' => $totalStudents,
            'total_courses' => $totalCourses,
            'total_instructors' => $totalInstructors,
            'monthly_revenue' => $monthlyRevenue,
            'top_courses' => $topCourses,
            'enrollment_trend' => $enrollmentTrend
        ]);
    }
}