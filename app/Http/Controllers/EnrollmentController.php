<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = Auth::user();
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
            
        if ($existingEnrollment) {
            return redirect()->back()->with('error', 'You are already enrolled in this course');
        }
        
        // Create enrollment (payment handled separately via checkout)
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'progress_percentage' => 0,
            'status' => 'active',
            'enrolled_at' => now(),
            'last_accessed_at' => now(),
            'amount_paid' => $course->price,
        ]);
        
        // Create notification for student
        Notification::create([
            'user_id' => $user->id,
            'type' => 'enrollment',
            'title' => '🎉 Enrollment Successful!',
            'message' => 'You have successfully enrolled in "' . $course->title . '". Start learning now!',
            'action_url' => route('course.learn', $course->slug),
        ]);
        
        // Notify instructor
        Notification::create([
            'user_id' => $course->instructor_id,
            'type' => 'new_student',
            'title' => '👨‍🎓 New Student Enrolled',
            'message' => $user->name . ' just enrolled in your course "' . $course->title . '"',
            'action_url' => route('instructor.courses.show', $course->id),
        ]);
        
        // Notify admin
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_enrollment',
                'title' => '📊 New Enrollment',
                'message' => $user->name . ' enrolled in "' . $course->title . '"',
                'action_url' => route('admin.dashboard'),
            ]);
        }
        
        // If course is free, redirect to learn page
        if ($course->price == 0) {
            return redirect()->route('course.learn', $course->slug)
                ->with('success', 'Successfully enrolled in the course!');
        }
        
        // For paid courses, redirect to checkout
        return redirect()->route('checkout.index', $course->slug)
            ->with('success', 'Please complete payment to access the course');
    }
    
    public function myCourses()
    {
        $enrollments = Enrollment::with(['course.instructor', 'course.category'])
            ->where('user_id', Auth::id())
            ->orderBy('last_accessed_at', 'desc')
            ->paginate(12);
            
        return Inertia::render('Student/MyCourses', [
            'enrollments' => $enrollments
        ]);
    }
    
    public function updateProgress(Request $request, $enrollmentId)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);
        
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $enrollment->update([
            'progress_percentage' => $request->progress,
            'last_accessed_at' => now(),
        ]);
        
        return response()->json(['success' => true]);
    }
}