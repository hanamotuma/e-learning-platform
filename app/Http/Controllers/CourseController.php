<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses);
    }
    
    public function show(Course $course)
    {
        return response()->json($course);
    }
    
    public function learn(Course $course)
    {
        $user = Auth::user();
        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();
        
        if (!$enrollment) {
            return redirect()->route('home')->with('error', 'You need to enroll first');
        }
        
        return Inertia::render('Course/Learn', [
            'course' => $course,
            'enrollment' => $enrollment
        ]);
    }
}