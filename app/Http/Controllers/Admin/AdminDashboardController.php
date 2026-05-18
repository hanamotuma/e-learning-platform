<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\CourseApproval;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\SupportTicket;
use App\Models\Review;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Overview Statistics
        $stats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'today_revenue' => Payment::where('status', 'completed')
                ->whereDate('created_at', Carbon::today())->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)->sum('amount'),
            'total_students' => User::role('student')->count(),
            'total_instructors' => User::role('instructor')->count(),
            'total_courses' => Course::count(),
            'pending_approvals' => Course::where('status', 'pending')->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->sum('amount'),
        ];

        // Revenue Chart Data
        $revenueChart = $this->getRevenueChartData();
        
        // Course Sales Chart
        $courseSalesChart = $this->getCourseSalesData();
        
        // Student Growth Chart
        $studentGrowth = $this->getStudentGrowthData();
        
        // Top Instructors
        $topInstructors = User::role('instructor')
            ->withCount('courses')
            ->withSum('courses as total_sales', 'price')
            ->orderBy('total_sales', 'desc')
            ->limit(5)
            ->get();
        
        // Top Selling Courses
        $topCourses = Course::with('instructor')
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();
        
        // Recent Activities
        $recentActivities = $this->getRecentActivities();
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'revenueChart' => $revenueChart,
            'courseSalesChart' => $courseSalesChart,
            'studentGrowth' => $studentGrowth,
            'topInstructors' => $topInstructors,
            'topCourses' => $topCourses,
            'recentActivities' => $recentActivities,
        ]);
    }
    
    private function getRevenueChartData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Payment::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            
            $data[] = [
                'month' => $date->format('M'),
                'revenue' => $revenue,
            ];
        }
        return $data;
    }
    
    private function getCourseSalesData()
    {
        return Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($course) {
                return [
                    'title' => $course->title,
                    'sales' => $course->enrollments_count,
                ];
            });
    }
    
    private function getStudentGrowthData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::role('student')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $data[] = [
                'month' => $date->format('M'),
                'students' => $count,
            ];
        }
        return $data;
    }
    
    private function getRecentActivities()
    {
        $activities = collect();
        
        // New enrollments
        $enrollments = Enrollment::with(['user', 'course'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($enrollment) {
                return [
                    'type' => 'enrollment',
                    'message' => "{$enrollment->user->full_name} enrolled in {$enrollment->course->title}",
                    'created_at' => $enrollment->created_at,
                ];
            });
        
        // New course submissions
        $courses = Course::with('instructor')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($course) {
                return [
                    'type' => 'course_submission',
                    'message' => "{$course->instructor->full_name} submitted a new course: {$course->title}",
                    'created_at' => $course->created_at,
                ];
            });
        
        return $enrollments->concat($courses)->sortByDesc('created_at')->take(10)->values();
    }
}