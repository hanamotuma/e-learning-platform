<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Discussion;
use App\Models\Activity;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get enrolled courses with progress from Enrollment table
        $enrolledCourses = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'progress' => $enrollment->progress_percentage ?? 0,
                    'instructor' => $enrollment->course->instructor->name ?? 'Unknown',
                    'thumbnail' => $enrollment->course->image, // using image as thumbnail
                    'lastAccessed' => $enrollment->updated_at->diffForHumans(),
                ];
            });
        
        // Get upcoming activities (you may need to create this logic based on your quiz/assignment tables)
        $upcomingActivities = $this->getUpcomingActivities($user->id);
        
        // Get recent discussions (adjust based on your discussions table)
        $discussions = $this->getRecentDiscussions($enrolledCourses->pluck('id'));
        
        // Calculate stats
        $stats = [
            'courses_enrolled' => $enrolledCourses->count(),
            'hours_learned' => $this->getTotalLearningHours($user->id),
            'certificates' => $this->getCertificatesCount($user->id),
            'current_streak' => $this->getLearningStreak($user->id),
        ];
        
        return inertia('Student/Dashboard', [
            'enrolledCourses' => $enrolledCourses,
            'upcomingActivities' => $upcomingActivities,
            'discussions' => $discussions,
            'stats' => $stats,
            'auth' => ['user' => $user]
        ]);
    }
    
    private function getTotalLearningHours($userId)
    {
        // Calculate total learning hours from lesson progress or video watch time
        // This is example logic - adjust based on your tables
        return DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->sum('time_spent_minutes') / 60 ?? 0;
    }
    
    private function getCertificatesCount($userId)
    {
        return DB::table('certificates')
            ->where('user_id', $userId)
            ->count();
    }
    
    private function getLearningStreak($userId)
    {
        // Calculate consecutive days with activity
        // Example logic - adjust based on your activity tracking
        return DB::table('user_activities')
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('COUNT(DISTINCT DATE(created_at)) as days'))
            ->value('days') ?? 0;
    }
    
    private function getUpcomingActivities($userId)
    {
        // Example: Get upcoming quizzes from quizzes table
        // Adjust this query based on your actual quiz/assignment structure
        return DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.user_id', $userId)
            ->where('quizzes.due_date', '>', now())
            ->whereNull('quiz_attempts.completed_at')
            ->select('quizzes.title', 'quizzes.due_date')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id ?? rand(),
                    'title' => $item->title,
                    'date' => date('M d, Y', strtotime($item->due_date)),
                    'time' => date('g:i A', strtotime($item->due_date)),
                    'type' => 'quiz',
                ];
            });
    }
    
    private function getRecentDiscussions($courseIds)
    {
        // Adjust based on your discussions table structure
        return DB::table('discussions')
            ->whereIn('course_id', $courseIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($discussion) {
                return [
                    'id' => $discussion->id,
                    'title' => $discussion->title,
                    'replies' => DB::table('discussion_replies')
                        ->where('discussion_id', $discussion->id)
                        ->count(),
                    'lastActive' => $discussion->updated_at->diffForHumans(),
                ];
            });
    }
}