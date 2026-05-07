<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\ProgressTracking;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonProgressController extends Controller
{
    public function markComplete(Request $request, $courseId, $lessonId)
    {
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();
        
        $progress = ProgressTracking::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
                'progress_percentage' => 100,
            ]
        );
        
        // Update overall course progress
        $this->updateCourseProgress($courseId, $user->id, $enrollment);
        
        // Notify instructor
        $course = Course::find($courseId);
        if ($course && $course->instructor_id) {
            \App\Models\Notification::create([
                'user_id' => $course->instructor_id,
                'type' => 'student_progress',
                'title' => '📚 Student Progress Update',
                'message' => $user->name . ' completed a lesson in "' . $course->title . '"',
                'action_url' => route('instructor.courses.show', $courseId),
            ]);
        }
        
        return response()->json(['success' => true, 'message' => 'Lesson marked as complete']);
    }
    
    private function updateCourseProgress($courseId, $userId, $enrollment)
    {
        $course = Course::with(['sections.lessons', 'sections.quizzes'])->find($courseId);
        
        $completedLessons = ProgressTracking::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'completed')
            ->count();
        
        $totalLessons = $course->sections->sum(function($s) { return $s->lessons->count(); });
        
        $passedQuizzes = QuizAttempt::where('user_id', $userId)
            ->where('is_passed', true)
            ->whereIn('quiz_id', $course->sections->flatMap->quizzes->pluck('id'))
            ->distinct('quiz_id')
            ->count();
        
        $totalQuizzes = $course->sections->sum(function($s) { return $s->quizzes->count(); });
        
        $totalItems = $totalLessons + $totalQuizzes;
        $completedItems = $completedLessons + $passedQuizzes;
        
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? 'completed' : 'active',
            'completed_at' => $progressPercentage >= 100 ? now() : $enrollment->completed_at,
        ]);
        
        // Generate certificate if completed
        if ($progressPercentage >= 100 && !$enrollment->certificate_issued) {
            $this->generateCertificate($enrollment);
        }
    }
    
    private function generateCertificate($enrollment)
    {
        $existingCertificate = \App\Models\Certificate::where('user_id', $enrollment->user_id)
            ->where('course_id', $enrollment->course_id)
            ->first();
        
        if (!$existingCertificate) {
            $certificateNumber = 'CERT-' . strtoupper(uniqid()) . '-' . $enrollment->id;
            
            $certificate = \App\Models\Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'certificate_number' => $certificateNumber,
                'issued_at' => now(),
                'certificate_url' => null,
            ]);
            
            $enrollment->update(['certificate_issued' => true]);
            
            \App\Models\Notification::create([
                'user_id' => $enrollment->user_id,
                'type' => 'certificate_issued',
                'title' => '🎓 Certificate Earned!',
                'message' => 'Congratulations! You have completed the course and earned your certificate.',
                'action_url' => route('certificate.show', $certificate->id),
            ]);
        }
    }
}