<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment; 
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. STATS: Revenue (Using 'amount' column)
        $currentMonthSales = Payment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
            
        $lastMonthSales = Payment::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        $salesIncrease = $this->calculateGrowth($currentMonthSales, $lastMonthSales);

        // 2. STATS: Active Students
        $currentStudents = User::whereHas('enrollments')->count();
        $lastMonthStudents = User::whereHas('enrollments', function($q) {
                $q->whereMonth('created_at', now()->subMonth()->month)
                  ->whereYear('created_at', now()->subMonth()->year);
            })->count();
        
        $studentIncrease = $this->calculateGrowth($currentStudents, $lastMonthStudents);

        // 3. USER MANAGEMENT (Fixed naming and status logic)
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->withCount('enrollments')
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($user) => [
                'id' => $user->id,
                'full_name' => $user->name,
                'email' => $user->email,
                // Ensure last_seen_at is cast to a date in User Model
                'status' => ($user->last_seen_at && Carbon::parse($user->last_seen_at)->gt(now()->subMinutes(5))) ? 'active' : 'offline',
                'last_login' => $user->last_seen_at ? Carbon::parse($user->last_seen_at)->diffForHumans() : 'Never',
                'course_enrollment' => $user->enrollments_count . ' ' . ($user->enrollments_count === 1 ? 'Course' : 'Courses'),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_sales' => (float) Payment::sum('amount'),
                'sales_increase' => $salesIncrease,
                'active_students' => $currentStudents,
                'student_increase' => $studentIncrease,
                'total_courses' => Course::count(),
                'course_increase' => $this->calculateGrowth(Course::count(), Course::where('created_at', '<', now()->subMonth())->count()),
            ],
            'users' => $users,
            'revenue_chart' => $this->getMonthlyRevenue(),
            'platform_overview' => [
                'students' => User::count(),
                'total_enrollment' => Enrollment::count(),
                'new_enrollments' => Enrollment::where('created_at', '>=', now()->startOfWeek())->count(),
                'total_courses' => Course::count(),
                'online_users' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count() ?? 0,
            ],
            'course_performance' => [
                'completion' => 90, 
                'engagement' => 77,
                'average_grade' => 88,
            ],
            // You MUST include this line!
        'notifications' => [
            'data' => Notification::where('user_id', auth()->id())
                        ->latest()
                        ->take(10)
                        ->get()
        ],
        ]);
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Optimized Chart Data: 
     * Fetches monthly totals for the current year.
     */
    private function getMonthlyRevenue()
    {
        // We use the current year to ensure old test data doesn't clutter or vanish
        return Payment::whereYear('created_at', now()->year)
            ->select(
                DB::raw('SUM(amount) as sum'),
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("MONTH(created_at) as month_num")
            )
            ->groupBy('month', 'month_num')
            ->orderBy('month_num', 'ASC')
            ->get()
            ->pluck('sum', 'month'); // Returns e.g., {"Jan": 200, "Feb": 500}
    }
}