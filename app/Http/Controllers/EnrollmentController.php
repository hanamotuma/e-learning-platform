<?php

namespace App\Domains\Enrollment\Controllers;

use App\Domains\Course\Models\Course;
use App\Domains\Enrollment\Models\Enrollment;
use App\Domains\Enrollment\Services\EnrollmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function store(Request $request, Course $course)
    {
        $enrollment = $this->enrollmentService->enrollUser(Auth::user(), $course);
        
        return redirect()->route('courses.learn', $course)
            ->with('success', 'Successfully enrolled in the course!');
    }

    public function updateProgress(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'is_completed' => 'required|boolean'
        ]);

        $this->enrollmentService->updateLessonProgress(
            Auth::user(),
            $request->lesson_id,
            $request->is_completed
        );

        return response()->json(['success' => true]);
    }
}