<?php

use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Login Required)
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Move this here so your Home.vue can always see the courses
Route::get('/courses', function () {
    $courses = Course::where('is_published', true)
        ->with(['instructor', 'category'])
        ->latest()
        ->get() // REMOVED take(12) - get ALL published courses
        ->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'originalPrice' => $course->price * 3,
                'image' => $course->image ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
                'instructor' => $course->instructor->full_name ?? 'Expert Instructor',
                'category' => $course->category->name ?? 'Development',
                'rating' => $course->rating ?? 4.8,
                'reviews' => $course->reviews_count ?? rand(1000, 20000),
                'students' => $course->students_count ?? rand(10000, 150000),
                'hours' => $course->duration_hours ?? rand(20, 60),
                'badge' => 'Featured',
                'level' => $course->difficulty_level ?? 'All Levels',
                'slug' => $course->slug,
                'inCart' => false,
            ];
        });
    
    return response()->json($courses);
});

    Route::get('/courses', [CourseController::class, 'featured'])->name('api.courses.featured');


/*
|--------------------------------------------------------------------------
| Protected Routes (Login Required via Sanctum)
|--------------------------------------------------------------------------
*/

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

    // Unified Enrollment Route
    Route::post('/enroll', function (Request $request) {
        $user = Auth::user();
        $courseId = $request->course_id;
        
        if (!$courseId) {
            return response()->json(['success' => false, 'message' => 'Course ID required'], 400);
        }
        
        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
        
        if ($existing) {
            return response()->json(['success' => true, 'already_enrolled' => true, 'message' => 'Already enrolled']);
        }
        
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
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
            
            return response()->json([
                'success' => true, 
                'enrollment' => $enrollment,
                'message' => 'Successfully enrolled in ' . $course->title
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

});