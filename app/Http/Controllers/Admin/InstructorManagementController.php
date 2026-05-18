<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Withdrawal;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InstructorManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('instructor')->withCount('courses');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status === 'suspended') {
            $query->where('is_active', false);
        } elseif ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'pending') {
            $query->where('is_approved', false);
        }
        
        $instructors = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return Inertia::render('Admin/Instructors/Index', [
            'instructors' => $instructors,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Admin/Instructors/Create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'bio' => 'nullable|string',
            'expertise' => 'nullable|string',
        ]);
        
        $username = Str::slug($request->full_name);
        $originalUsername = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        $instructor = User::create([
            'full_name' => $request->full_name,
            'username' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'expertise' => $request->expertise,
            'is_active' => true,
            'is_approved' => true,
        ]);
        
        $instructor->assignRole('instructor');
        
        return redirect()->route('admin.instructors.index')->with('success', 'Instructor created successfully');
    }
    
    public function show($id)
    {
        $instructor = User::role('instructor')
            ->with(['courses.enrollments', 'courses.reviews'])
            ->findOrFail($id);
        
        $courses = $instructor->courses()->withCount('enrollments')->get();
        $earnings = $instructor->courses()->with('enrollments')->get()->sum(function($course) {
            return $course->enrollments->sum('amount_paid') * 0.9;
        });
        
        $withdrawals = Withdrawal::where('user_id', $id)->latest()->get();
        
        return Inertia::render('Admin/Instructors/Show', [
            'instructor' => $instructor,
            'courses' => $courses,
            'earnings' => $earnings,
            'withdrawals' => $withdrawals,
        ]);
    }
    
    public function approve($id)
    {
        $instructor = User::role('instructor')->findOrFail($id);
        $instructor->update(['is_approved' => true]);
        
        Notification::create([
            'user_id' => $instructor->id,
            'type' => 'instructor_approved',
            'title' => 'Instructor Application Approved',
            'message' => 'Congratulations! Your instructor application has been approved. You can now create and publish courses.',
            'action_url' => route('instructor.dashboard'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function reject($id)
    {
        $instructor = User::role('instructor')->findOrFail($id);
        $instructor->update(['is_approved' => false]);
        
        Notification::create([
            'user_id' => $instructor->id,
            'type' => 'instructor_rejected',
            'title' => 'Instructor Application Update',
            'message' => 'Your instructor application has been reviewed. Please contact support for more information.',
            'action_url' => route('support.index'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function toggleStatus($id)
    {
        $instructor = User::role('instructor')->findOrFail($id);
        $instructor->update(['is_active' => !$instructor->is_active]);
        
        $status = $instructor->is_active ? 'activated' : 'suspended';
        
        Notification::create([
            'user_id' => $instructor->id,
            'type' => 'account_status',
            'title' => "Account {$status}",
            'message' => "Your instructor account has been {$status} by an administrator.",
            'action_url' => route('instructor.dashboard'),
        ]);
        
        return response()->json(['success' => true, 'is_active' => $instructor->is_active]);
    }
    
    public function earnings($id)
    {
        $instructor = User::role('instructor')->findOrFail($id);
        
        $sales = Course::where('instructor_id', $id)
            ->with('enrollments')
            ->get()
            ->map(function($course) {
                return [
                    'course_title' => $course->title,
                    'sales_count' => $course->enrollments->count(),
                    'revenue' => $course->enrollments->sum('amount_paid'),
                    'commission' => $course->enrollments->sum('amount_paid') * 0.1,
                    'earnings' => $course->enrollments->sum('amount_paid') * 0.9,
                ];
            });
        
        return response()->json(['sales' => $sales]);
    }
}