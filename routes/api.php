<?php

use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Get user enrollments
    Route::get('/user/enrollments', function () {
        $enrollments = Enrollment::with(['course.instructor'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $enrollments,
            'total' => $enrollments->count()
        ]);
    });
    
    // Save enrollment after payment
    Route::post('/enroll', function (Request $request) {
        $user = Auth::user();
        $courseId = $request->course_id;
        
        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => true, 
                'already_enrolled' => true,
                'message' => 'Already enrolled'
            ]);
        }
        
        $course = Course::find($courseId);
        
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'progress_percentage' => 0,
            'status' => 'active',
            'enrolled_at' => now(),
            'last_accessed_at' => now(),
            'amount_paid' => $course ? $course->price : $request->amount,
        ]);
        
        return response()->json([
            'success' => true,
            'enrollment' => $enrollment,
            'message' => 'Successfully enrolled'
        ]);
    });
});