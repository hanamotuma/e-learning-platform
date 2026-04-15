<?php

namespace App\Domains\Course\Services;

use App\Domains\Course\Models\Course;
use App\Domains\Enrollment\Models\LessonProgress;
use Illuminate\Support\Facades\DB;

class CourseService
{
    public function getCourseContent(Course $course, $userId)
    {
        $course->load(['sections.lessons']);
        
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();
            
        return [
            'course' => $course,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $course->lessons->count(),
            'completed_count' => count($completedLessons),
            'progress_percentage' => $course->lessons->count() > 0 
                ? (count($completedLessons) / $course->lessons->count()) * 100 
                : 0
        ];
    }

    public function getRecommendedCourses($userId, $limit = 6)
    {
        // Get courses based on user's enrolled categories and preferences
        return Course::where('is_published', true)
            ->whereDoesntHave('enrollments', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['instructor', 'category'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
