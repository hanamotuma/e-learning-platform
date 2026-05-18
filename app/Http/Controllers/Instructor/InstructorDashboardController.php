<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
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
            'pending' => $courses->where('approval_status', 'pending')->count(),
            'approved' => $courses->where('approval_status', 'approved')->count(),
            'rejected' => $courses->where('approval_status', 'rejected')->count(),
            'total_students' => $courses->sum(function($course) {
                return $course->enrollments->count();
            }),
            'total_revenue' => $courses->sum(function($course) {
                return $course->enrollments->sum('amount_paid');
            }),
            'average_rating' => round($courses->avg('rating') ?? 0, 1),
            'total_reviews' => Review::whereIn('course_id', $courses->pluck('id'))->count(),
        ];
        
        // Get recent enrollments
        $recentEnrollments = Enrollment::with(['course', 'user'])
            ->whereIn('course_id', $courses->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(10)
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
            'recentEnrollments' => Enrollment::with(['user', 'course'])
                   ->whereIn('course_id', $courses->pluck('id'))
                   ->orderBy('created_at', 'desc')->limit(5)->get(),
            'recentReviews' => Review::with(['user', 'course'])
                   ->whereIn('course_id', $courses->pluck('id'))
                   ->orderBy('created_at', 'desc')->limit(5)->get(),
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
    
    public function students()
    {
        $courses = Course::where('instructor_id', Auth::id())->pluck('id');
        $students = Enrollment::with(['user', 'course'])
            ->whereIn('course_id', $courses)
            ->paginate(20);
        
        return Inertia::render('Instructor/Students', ['students' => $students]);
    }
        
    public function earnings()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        $totalEarnings = Enrollment::whereIn('course_id', $courses->pluck('id'))->sum('amount_paid');
        
        return Inertia::render('Instructor/Earnings', [
            'totalEarnings' => $totalEarnings,
            'platformFee' => $totalEarnings * 0.3,
            'availableBalance' => $totalEarnings * 0.7,
            'transactions' => Payment::whereIn('course_id', $courses->pluck('id'))->paginate(20)
        ]);
    }

    public function reviews()
    {
        $courses = Course::where('instructor_id', Auth::id())->pluck('id');
        $reviews = Review::with(['user', 'course'])->whereIn('course_id', $courses)->latest()->get();
        
        return Inertia::render('Instructor/Reviews', ['reviews' => $reviews]);
    }

    public function getReviewsData()
    {
        $courses = Course::where('instructor_id', Auth::id())->pluck('id');
        $reviews = Review::with(['user', 'course'])->whereIn('course_id', $courses)->latest()->get();
        
        return response()->json(['reviews' => $reviews]);
    }

    public function analytics()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        $courseIds = $courses->pluck('id');
        
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)->count();
        $totalRevenue = Enrollment::whereIn('course_id', $courseIds)->sum('amount_paid');
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();
        
        $popularCourses = $courses->map(function($course) {
            $students = $course->enrollments->count();
            return [
                'id' => $course->id,
                'title' => $course->title,
                'students' => $students,
                'revenue' => $students * $course->price
            ];
        })->sortByDesc('students')->values();
        
        return Inertia::render('Instructor/Analytics', [
            'analytics' => [
                'total_views' => $totalEnrollments,
                'total_students' => $totalStudents,
                'total_revenue' => $totalRevenue,
                'total_courses' => $courses->count(),
                'completion_rate' => $totalStudents > 0 ? round(($totalStudents / $totalEnrollments) * 100) : 0,
                'popular_courses' => $popularCourses
            ]
        ]);
    }

    public function getAnalyticsData()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();
        $courseIds = $courses->pluck('id');
        
        return response()->json([
            'total_views' => Enrollment::whereIn('course_id', $courseIds)->count(),
            'total_students' => Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count(),
            'total_revenue' => Enrollment::whereIn('course_id', $courseIds)->sum('amount_paid'),
            'total_courses' => $courses->count(),
            'completion_rate' => 75,
            'popular_courses' => []
        ]);
    }

    public function settings()
    {
        return Inertia::render('Instructor/Settings', ['user' => Auth::user()]);
    }

    public function help()
    {
        return Inertia::render('Instructor/Help');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio'   => 'nullable|string|max:1000',
        ]);

        // Explicitly map validated 'name' key to 'full_name' column
        $user->update([
            'full_name' => $validated['name'],
            'phone' => $validated['phone'],
            'bio' => $validated['bio'],
        ]);
        
        // Safely update or create the related instructor profile setup
        $user->instructorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $validated['phone'],
                'bio'   => $validated['bio'],
            ]
        );
        
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture_url = '/storage/' . $path;
            $user->save();
        }
        
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
        
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->back()->with('success', 'Password updated successfully');
    }
}