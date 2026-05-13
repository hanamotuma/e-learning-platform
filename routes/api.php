<?php

use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public test route (no auth needed)
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
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
    
    // Enroll in course after payment
    Route::post('/enroll', function (Request $request) {
        \Log::info('Enroll API called', $request->all());
        
        $user = Auth::user();
        $courseId = $request->course_id;
        
        if (!$courseId) {
            return response()->json([
                'success' => false,
                'message' => 'Course ID is required'
            ], 400);
        }
        
        // Check if already enrolled
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
        
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }
        
        try {
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'progress_percentage' => 0,
                'status' => 'active',
                'enrolled_at' => now(),
                'last_accessed_at' => now(),
                'amount_paid' => $course->price,
            ]);
            
            \Log::info('Enrollment created', ['enrollment_id' => $enrollment->id]);
            
            return response()->json([
                'success' => true,
                'enrollment' => $enrollment,
                'message' => 'Successfully enrolled in ' . $course->title
            ]);
        } catch (\Exception $e) {
            \Log::error('Enrollment creation failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
});