<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function myCourses()
    {
        $user = Auth::user();
        $enrollments = $user->enrollments()->with('course')->orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Student/MyCourses', [
            'enrollments' => $enrollments
        ]);
    }
    
    public function enroll(Course $course)
    {
        $user = Auth::user();
        
        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return back()->with('error', 'Already enrolled');
        }
        
        DB::transaction(function () use ($user, $course) {
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'progress' => 0,
                'completed' => false,
                'enrolled_at' => now(),
                'payment_status' => 'completed',
                'amount_paid' => $course->price
            ]);
            $course->increment('students');
        });
        
        return redirect()->route('my-courses')->with('success', 'Enrolled successfully');
    }
    
    public function completeEnrollment(Request $request)
    {
        $request->validate([
            'course_ids' => 'required|array',
            'amount' => 'required|numeric'
        ]);
        
        $user = Auth::user();
        $courseIds = $request->course_ids;
        
        DB::transaction(function () use ($user, $courseIds) {
            foreach ($courseIds as $courseId) {
                $course = Course::find($courseId);
                if ($course && !$user->courses()->where('course_id', $courseId)->exists()) {
                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $courseId,
                        'progress' => 0,
                        'completed' => false,
                        'enrolled_at' => now(),
                        'payment_status' => 'completed',
                        'amount_paid' => $course->price
                    ]);
                    $course->increment('students');
                }
            }
        });
        
        // Clear cart
        session()->forget('cart');
        
        return response()->json(['success' => true]);
    }
}