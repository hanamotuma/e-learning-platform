<?php

use App\Http\Controllers\PaymentController;
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
            'data' => $enrollments,
            'total' => $enrollments->count()
        ]);
    });
    
    // Enroll in course after payment
    Route::post('/courses/{id}/enroll', function ($id) {
        $user = Auth::user();
        $course = Course::findOrFail($id);
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        
        if ($existingEnrollment) {
            return response()->json([
                'success' => true,
                'already_enrolled' => true,
                'message' => 'Already enrolled'
            ]);
        }
        
        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'progress_percentage' => 0,
            'status' => 'active',
            'enrolled_at' => now(),
            'last_accessed_at' => now(),
            'amount_paid' => $course->price,
        ]);
        
        return response()->json([
            'success' => true,
            'enrollment' => $enrollment,
            'message' => 'Successfully enrolled'
        ]);
    });
});

Route::post('/payment/telebirr', [PaymentController::class, 'processTelebirr']);
Route::post('/payment/cbe', [PaymentController::class, 'processCBE']);
Route::post('/payment/chapa/initialize', [PaymentController::class, 'initializeChapa']);
Route::middleware(['auth:sanctum'])->group(function () {
    // Get user enrollments
    Route::get('/user/enrollments', function () {
        $enrollments = \App\Models\Enrollment::with(['course.instructor'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $enrollments,
            'total' => $enrollments->count()
        ]);
    });
});