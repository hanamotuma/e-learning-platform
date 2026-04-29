<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $stats = [
            'total_sales' => Enrollment::where('payment_status', 'completed')->sum('amount'),
            'sales_increase' => $this->getSalesGrowth(),
            'active_students' => User::where('role', 'student')->where('is_active', true)->count(),
            'student_increase' => $this->getStudentGrowth(),
            'total_courses' => Course::count(),
            'course_increase' => $this->getCourseGrowth(),
        ];
        
        // Users list with pagination
        $users = User::withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->is_active ? 'active' : 'inactive',
                    'last_login' => $user->last_login_at?->diffForHumans() ?? 'Never',
                    'course_enrollment' => $user->enrollments_count . ' courses',
                ];
            });
        
        // Revenue chart data (last 30 days)
        $revenueChart = $this->getRevenueChartData();
        
        // Platform overview
        $platformOverview = [
            'thoughts' => Review::count(),
            'students' => User::where('role', 'student')->count(),
            'total_enrollment' => Enrollment::count(),
            'new_enrollments' => Enrollment::where('created_at', '>=', now()->subDays(7))->count(),
            'total_courses' => Course::count(),
            'completion_rate' => $this->getCompletionRate(),
            'engagement_rate' => $this->getEngagementRate(),
            'average_grade' => $this->getAverageGrade(),
        ];
        
        // Course performance
        $coursePerformance = [
            'completion' => $this->getCompletionRate(),
            'engagement' => $this->getEngagementRate(),
            'average_grade' => $this->getAverageGrade(),
        ];
        
        return inertia('Admin/Dashboard', [
            'stats' => $stats,
            'users' => $users,
            'revenue_chart' => $revenueChart,
            'platform_overview' => $platformOverview,
            'course_performance' => $coursePerformance,
            'auth' => ['user' => auth::user()]
        ]);
    }
    
    private function getSalesGrowth()
    {
        $currentMonth = Enrollment::whereMonth('created_at', now()->month)
            ->where('payment_status', 'completed')
            ->sum('amount');
        
        $lastMonth = Enrollment::whereMonth('created_at', now()->subMonth()->month)
            ->where('payment_status', 'completed')
            ->sum('amount');
        
        if ($lastMonth == 0) return 100;
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100);
    }
    
    private function getStudentGrowth()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 100;
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100);
    }
    
    private function getCourseGrowth()
    {
        $currentMonth = Course::whereMonth('created_at', now()->month)->count();
        $lastMonth = Course::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 100;
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100);
    }
    
    private function getRevenueChartData()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[$date->format('Y-m-d')] = Enrollment::whereDate('created_at', $date)
                ->where('payment_status', 'completed')
                ->sum('amount');
        }
        return $data;
    }
    
    private function getCompletionRate()
    {
        return round(Enrollment::where('progress', '>=', 100)->count() / max(Enrollment::count(), 1) * 100);
    }
    
    private function getEngagementRate()
    {
        // Example: users who logged in within last 7 days
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(7))->count();
        return round(($activeUsers / max(User::count(), 1)) * 100);
    }
    
    private function getAverageGrade()
    {
        return round(DB::table('quiz_attempts')->avg('score') ?? 0);
    }
}