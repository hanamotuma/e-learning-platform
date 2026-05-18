<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\ProgressTracking;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\Course;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if(!$user){
            return redirect()->route('login');
        }
                
        // Get all active enrollments with course details
        $enrollments = Enrollment::with(['course.instructor', 'course.category'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate comprehensive statistics
        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->filter(function($e) {
            return $e->progress_percentage >= 100;
        })->count();
        
        $inProgressCourses = $enrollments->filter(function($e) {
            return $e->progress_percentage > 0 && $e->progress_percentage < 100;
        })->count();
        
        $notStartedCourses = $enrollments->filter(function($e) {
            return $e->progress_percentage == 0;
        })->count();
        
        $averageProgress = $totalCourses > 0 ? round($enrollments->avg('progress_percentage')) : 0;
        
        // Total learning hours
        $totalHours = $enrollments->sum(function($e) {
            return $e->course->duration_hours ?? 0;
        });
        
        // Get certificates
        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        
        // Get wishlist items
        $wishlist = Wishlist::with('course')
            ->where('user_id', $user->id)
            ->get();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user->id);
        
        // Get recent quiz attempts
        $recentQuizzes = QuizAttempt::with('quiz')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest()
            ->take(5)
            ->get();
        
        // Get unread notifications count
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        // Get continue learning course (first incomplete course)
        $continueCourse = $enrollments->filter(function($e) {
            return $e->progress_percentage > 0 && $e->progress_percentage < 100;
        })->first();
        
        // Weekly learning data for chart
        $weeklyData = $this->getWeeklyLearningData($user->id);
        
        // Monthly learning data
        $monthlyData = $this->getMonthlyLearningData($user->id);
        
        // Get achievements/badges
        $achievements = $this->getAchievements($user->id, $totalCourses, $completedCourses);
        
        // Get upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines($user->id);
        
        // Get recommended courses
        $recommendedCourses = $this->getRecommendedCourses($user->id);
        
        // Get learning streak
        $learningStreak = $this->getLearningStreak($user->id);
        
        return Inertia::render('Student/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'avatar' => $user->profile_picture_url,
                'joined_date' => $user->created_at->format('F Y'),
                'learning_streak' => $learningStreak,
            ],
            'stats' => [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'in_progress' => $inProgressCourses,
                'not_started' => $notStartedCourses,
                'average_progress' => $averageProgress,
                'certificates_earned' => $certificates->count(),
                'total_hours' => $totalHours,
                'quiz_taken' => QuizAttempt::where('user_id', $user->id)->count(),
                'wishlist_count' => $wishlist->count(),
            ],
            'enrollments' => $enrollments->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_id' => $enrollment->course_id,
                    'title' => $enrollment->course->title,
                    'description' => $enrollment->course->description,
                    'image' => $enrollment->course->image ?? '/images/default-course.jpg',
                    'instructor' => $enrollment->course->instructor->full_name ?? 'Expert Instructor',
                    'instructor_avatar' => $enrollment->course->instructor->profile_picture_url,
                    'progress' => $enrollment->progress_percentage ?? 0,
                    'last_accessed' => $enrollment->last_accessed_at,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'rating' => $enrollment->course->rating ?? 4.5,
                    'level' => $enrollment->course->difficulty_level ?? 'Beginner',
                    'hours' => $enrollment->course->duration_hours ?? 0,
                    'category' => $enrollment->course->category->name ?? 'General',
                    'total_lessons' => $enrollment->course->lessons_count ?? 0,
                    'completed_lessons' => ProgressTracking::where('user_id', Auth::id())
                        ->where('course_id', $enrollment->course_id)
                        ->where('status', 'completed')
                        ->count(),
                ];
            }),
            'certificates' => $certificates->map(function($certificate) {
                return [
                    'id' => $certificate->id,
                    'course_id' => $certificate->course_id,
                    'course_title' => $certificate->course->title,
                    'course_image' => $certificate->course->image,
                    'certificate_number' => $certificate->certificate_number,
                    'issued_at' => $certificate->issued_at,
                    'certificate_url' => $certificate->certificate_url,
                ];
            }),
            'wishlist' => $wishlist->map(function($item) {
                return [
                    'id' => $item->id,
                    'course_id' => $item->course_id,
                    'title' => $item->course->title,
                    'image' => $item->course->image,
                    'price' => $item->course->price,
                    'instructor' => $item->course->instructor->full_name ?? 'Expert',
                ];
            }),
            'recentActivities' => $recentActivities,
            'recentQuizzes' => $recentQuizzes,
            'unreadNotifications' => $unreadNotifications,
            'continueCourse' => $continueCourse ? [
                'id' => $continueCourse->course_id,
                'title' => $continueCourse->course->title,
                'image' => $continueCourse->course->image,
                'progress' => $continueCourse->progress_percentage,
                'next_lesson' => $this->getNextLesson($continueCourse->course_id, Auth::id()),
            ] : null,
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData,
            'achievements' => $achievements,
            'upcomingDeadlines' => $upcomingDeadlines,
            'recommendedCourses' => $recommendedCourses,
        ]);
    }

    
    private function getRecentActivities($userId)
    {
        $activities = collect();
        
        // Course enrollments
        $enrollments = Enrollment::with('course')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($e) {
                return [
                    'type' => 'enrollment',
                    'title' => 'New Course Enrolled',
                    'message' => "You enrolled in {$e->course->title}",
                    'icon' => 'BookOpen',
                    'color' => 'blue',
                    'date' => $e->created_at,
                    'time_ago' => Carbon::parse($e->created_at)->diffForHumans(),
                ];
            });
        
        // Lesson completions
        $completions = ProgressTracking::with('lesson')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($p) {
                return [
                    'type' => 'completion',
                    'title' => 'Lesson Completed',
                    'message' => "You completed \"{$p->lesson->title}\"",
                    'icon' => 'CheckCircle',
                    'color' => 'green',
                    'date' => $p->completed_at,
                    'time_ago' => Carbon::parse($p->completed_at)->diffForHumans(),
                ];
            });
        
        // Quiz completions
        $quizzes = QuizAttempt::with('quiz')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($q) {
                return [
                    'type' => 'quiz',
                    'title' => $q->is_passed ? 'Quiz Passed! 🎉' : 'Quiz Completed',
                    'message' => "You scored {$q->score}% on {$q->quiz->title}",
                    'icon' => 'Award',
                    'color' => $q->is_passed ? 'green' : 'yellow',
                    'date' => $q->completed_at,
                    'time_ago' => Carbon::parse($q->completed_at)->diffForHumans(),
                ];
            });
        
        // Certificates earned
        $certificates = Certificate::with('course')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($c) {
                return [
                    'type' => 'certificate',
                    'title' => 'Certificate Earned',
                    'message' => "You earned a certificate for {$c->course->title}",
                    'icon' => 'Award',
                    'color' => 'purple',
                    'date' => $c->issued_at,
                    'time_ago' => Carbon::parse($c->issued_at)->diffForHumans(),
                ];
            });
        
        return $enrollments->concat($completions)->concat($quizzes)->concat($certificates)
            ->sortByDesc('date')->take(15)->values();
    }
    
    private function getWeeklyLearningData($userId)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $hours = ProgressTracking::where('user_id', $userId)
                ->whereDate('last_accessed_at', $date->toDateString())
                ->sum('duration_minutes') / 60;
            
            $data[] = [
                'day' => $date->format('D'),
                'full_date' => $date->format('M d'),
                'hours' => round($hours, 1),
                'lessons' => ProgressTracking::where('user_id', $userId)
                    ->whereDate('completed_at', $date->toDateString())
                    ->count(),
            ];
        }
        return $data;
    }
    
    private function getMonthlyLearningData($userId)
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $hours = ProgressTracking::where('user_id', $userId)
                ->whereYear('last_accessed_at', $date->year)
                ->whereMonth('last_accessed_at', $date->month)
                ->sum('duration_minutes') / 60;
            
            $data[] = [
                'month' => $date->format('M'),
                'year' => $date->year,
                'hours' => round($hours, 1),
                'courses' => Enrollment::where('user_id', $userId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $data;
    }
    
    private function getAchievements($userId, $totalCourses, $completedCourses)
    {
        $achievements = [];
        
        // First course completed
        if ($completedCourses >= 1) {
            $achievements[] = [
                'name' => 'First Steps',
                'description' => 'Completed your first course',
                'icon' => '🎉',
                'earned' => true,
                'date' => Enrollment::where('user_id', $userId)
                    ->where('progress_percentage', '>=', 100)
                    ->first()?->completed_at,
            ];
        }
        
        // 5 courses completed
        if ($completedCourses >= 5) {
            $achievements[] = [
                'name' => 'Knowledge Seeker',
                'description' => 'Completed 5 courses',
                'icon' => '📚',
                'earned' => true,
            ];
        }
        
        // 10 courses completed
        if ($completedCourses >= 10) {
            $achievements[] = [
                'name' => 'Master Learner',
                'description' => 'Completed 10 courses',
                'icon' => '🏆',
                'earned' => true,
            ];
        }
        
        // Perfect quiz score
        $perfectQuiz = QuizAttempt::where('user_id', $userId)
            ->where('score', 100)
            ->exists();
        if ($perfectQuiz) {
            $achievements[] = [
                'name' => 'Perfect Score',
                'description' => 'Got 100% on a quiz',
                'icon' => '⭐',
                'earned' => true,
            ];
        }
        
        // Add upcoming achievements
        $achievements[] = [
            'name' => '5 Course Club',
            'description' => 'Complete 5 courses',
            'icon' => '🎯',
            'earned' => $completedCourses >= 5,
            'progress' => min(100, ($completedCourses / 5) * 100),
            'current' => $completedCourses,
            'target' => 5,
        ];
        
        $achievements[] = [
            'name' => 'Quiz Master',
            'description' => 'Get 100% on 3 quizzes',
            'icon' => '📝',
            'earned' => QuizAttempt::where('user_id', $userId)->where('score', 100)->count() >= 3,
            'progress' => min(100, (QuizAttempt::where('user_id', $userId)->where('score', 100)->count() / 3) * 100),
            'current' => QuizAttempt::where('user_id', $userId)->where('score', 100)->count(),
            'target' => 3,
        ];
        
        return $achievements;
    }
    
    private function getUpcomingDeadlines($userId)
    {
        // Get quiz deadlines, assignment due dates, etc.
        $deadlines = [];
        
        // Get quizzes that need to be taken
        $pendingQuizzes = QuizAttempt::where('user_id', $userId)
            ->whereNull('completed_at')
            ->with('quiz')
            ->get();
        
        foreach ($pendingQuizzes as $attempt) {
            $deadlines[] = [
                'type' => 'quiz',
                'title' => $attempt->quiz->title,
                'course' => $attempt->quiz->course->title,
                'due_date' => $attempt->quiz->due_date,
                'priority' => 'medium',
            ];
        }
        
        return array_slice($deadlines, 0, 5);
    }
    
    private function getRecommendedCourses($userId)
    {
        // Get user's categories interest
        $userCategories = Enrollment::with('course.category')
            ->where('user_id', $userId)
            ->get()
            ->pluck('course.category_id')
            ->unique()
            ->toArray();
        
        // Recommend courses from same categories not enrolled
        $enrolledIds = Enrollment::where('user_id', $userId)->pluck('course_id')->toArray();
        
        $recommended = Course::where('is_published', true)
            ->whereNotIn('id', $enrolledIds)
            ->when(!empty($userCategories), function($q) use ($userCategories) {
                $q->whereIn('category_id', $userCategories);
            })
            ->with(['instructor', 'category'])
            ->latest()
            ->take(4)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'image' => $course->image ?? '/images/default-course.jpg',
                    'price' => $course->price,
                    'instructor' => $course->instructor->full_name ?? 'Expert',
                    'rating' => $course->rating ?? 4.5,
                    'hours' => $course->duration_hours ?? 0,
                ];
            });
        
        return $recommended;
    }
    
    private function getLearningStreak($userId)
    {
        $lastActivity = ProgressTracking::where('user_id', $userId)
            ->latest('last_accessed_at')
            ->first();
        
        if (!$lastActivity || !$lastActivity->last_accessed_at) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = Carbon::now()->startOfDay();
        $lastDate = Carbon::parse($lastActivity->last_accessed_at)->startOfDay();
        
        if ($lastDate->diffInDays($currentDate) > 1) {
            return 0;
        }
        
        // Calculate continuous days of activity
        for ($i = 0; $i < 30; $i++) {
            $date = $currentDate->copy()->subDays($i);
            $hasActivity = ProgressTracking::where('user_id', $userId)
                ->whereDate('last_accessed_at', $date)
                ->exists();
            
            if ($hasActivity) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    private function getNextLesson($courseId, $userId)
    {
        $completedLessons = ProgressTracking::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();
        
        $nextLesson = \App\Models\Lesson::where('course_id', $courseId)
            ->whereNotIn('id', $completedLessons)
            ->orderBy('order')
            ->first();
        
        return $nextLesson ? [
            'id' => $nextLesson->id,
            'title' => $nextLesson->title,
        ] : null;
    }
}