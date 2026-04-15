<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\Enrollment;
use App\Models\ProgressTracking;
use App\Models\Certificate;
use App\Jobs\GenerateCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuizController extends Controller
{
    protected $quizService;
    public function __construct()
    {
        $this->quizService = new \App\Services\QuizService();
    }
    public function show($id)
    {
        $quiz = Quiz::with(['section', 'course', 'questions'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->first();
        
        if (!$enrollment || $enrollment->status === 'dropped') {
            return redirect()->route('courses.show', $quiz->course_id)
                ->with('error', 'You need to enroll in this course to take the quiz.');
        }
        
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $hasPassed = $attempts->contains('is_passed', true);
        $maxAttempts = 3;
        $attemptsCount = $attempts->count();
        $canAttempt = $attemptsCount < $maxAttempts && !$hasPassed;
        
        return Inertia::render('Quizzes/Show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'hasPassed' => $hasPassed,
            'canAttempt' => $canAttempt,
            'remainingAttempts' => max(0, $maxAttempts - $attemptsCount),
        ]);
    }
    
    
    public function start($id)
    {
        $quiz = Quiz::findOrFail($id);
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->exists();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }
        
        $hasPassed = QuizAttempt::where('quiz_id', $id)
            ->where('user_id', $user->id)
            ->where('is_passed', true)
            ->exists();
        
        if ($hasPassed) {
            return redirect()->back()->with('error', 'You have already passed this quiz.');
        }
        
        $attemptsCount = QuizAttempt::where('quiz_id', $id)
            ->where('user_id', $user->id)
            ->count();
        
        if ($attemptsCount >= 3) {
            return redirect()->back()->with('error', 'You have reached the maximum number of attempts.');
        }
        
        $attempt = QuizAttempt::create([
            'quiz_id' => $id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        
        // Note: Using 'id' as standard. Change to 'attempt_id' if your migration specifies it.
        return redirect()->route('quizzes.take', $attempt->id);
    }
    
    /**
     * View for taking the actual quiz.
     */
    public function take($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.questions', 'quiz.course'])
            ->findOrFail($attemptId);
        
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($attempt->completed_at) {
            return redirect()->route('quizzes.show', $attempt->quiz_id)
                ->with('info', 'This quiz attempt has already been completed.');
        }
        
        $quiz = $attempt->quiz;
        $timeRemaining = null;

        if ($quiz->time_limit_minutes) {
            $timeElapsed = now()->diffInMinutes($attempt->started_at);
            $timeRemaining = max(0, $quiz->time_limit_minutes - $timeElapsed);
            
            if ($timeRemaining <= 0) {
                // Auto-submit with empty request if time is up
                return $this->submit($attemptId, new Request());
            }
        }
        
        // Shuffle questions for variety, but keep them consistent for the same attempt session if needed
        $questions = $quiz->questions->shuffle();
        
        return Inertia::render('Quizzes/Take', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'questions' => $questions,
            'timeRemaining' => $timeRemaining,
            'totalQuestions' => $questions->count(),
        ]);
    }
    
    /**
     * Process the quiz submission.
     */
    public function submit($attemptId, Request $request)
    {
        $attempt = QuizAttempt::with(['quiz.questions'])
            ->findOrFail($attemptId);
        
        if ($attempt->completed_at) {
            return redirect()->route('quizzes.results', $attempt->id);
        }
        
        $answers = $request->input('answers', []);
        $quiz = $attempt->quiz;
        $questions = $quiz->questions;
        
        $score = 0;
        $totalPoints = 0;
        $results = [];
        
        foreach ($questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $this->checkAnswer($question, $userAnswer);
            
            if ($isCorrect) {
                $score += $question->points;
            }
            
            $results[$question->id] = [
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'correct_answer' => $question->correct_answer,
                'points' => $question->points,
                'explanation' => $question->explanation,
            ];
        }
        
        $percentage = ($totalPoints > 0) ? ($score / $totalPoints) * 100 : 0;
        $isPassed = $percentage >= ($quiz->passing_score ?? 70);
        
        $attempt->update([
            'score' => round($percentage, 2),
            'answers' => $results, // Ensure this is cast to 'array' in your Model
            'is_passed' => $isPassed,
            'completed_at' => now(),
        ]);
        
        if ($isPassed) {
            $enrollment = Enrollment::where('user_id', Auth::id())
                ->where('course_id', $quiz->course_id)
                ->first();
            
            if ($enrollment) {
                $this->updateCourseProgress($enrollment);
            }
        }
        
        return redirect()->route('quizzes.results', $attempt->id);
    }
    
    public function results($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.course', 'quiz.questions'])
            ->findOrFail($attemptId);
        
        if (!$attempt->completed_at) {
            return redirect()->route('quizzes.take', $attemptId);
        }
        
        return Inertia::render('Quizzes/Results', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'results' => $attempt->answers,
        ]);
    }
    
    private function checkAnswer($question, $userAnswer)
    {
        if (is_null($userAnswer)) return false;

        $correctAnswer = $question->correct_answer;
        
        switch ($question->question_type) {
            case 'multiple_choice':
            case 'true_false':
                return trim($userAnswer) == trim($correctAnswer);
            case 'short_answer':
                return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
            default:
                return false;
        }
    }
    
    private function updateCourseProgress(Enrollment $enrollment)
    {
        $course = $enrollment->course()->with(['sections.lessons', 'sections.quizzes'])->first();
        
        $totalLessons = 0;
        $totalQuizzes = 0;

        foreach ($course->sections as $section) {
            $totalLessons += $section->lessons->count();
            $totalQuizzes += $section->quizzes->count();
        }

        $totalItems = $totalLessons + $totalQuizzes;
        
        $completedLessons = ProgressTracking::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();
            
        $passedQuizzes = QuizAttempt::where('user_id', $enrollment->user_id)
            ->whereIn('quiz_id', $course->sections->flatMap->quizzes->pluck('id'))
            ->where('is_passed', true)
            ->distinct('quiz_id')
            ->count();
        
        $completedItems = $completedLessons + $passedQuizzes;
        $progressPercentage = $totalItems > 0 ? min(100, round(($completedItems / $totalItems) * 100)) : 0;
        
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? 'completed' : 'in_progress',
            'completed_at' => ($progressPercentage >= 100 && !$enrollment->completed_at) ? now() : $enrollment->completed_at,
        ]);
        
        if ($progressPercentage >= 100) {
            $this->generateCertificate($enrollment);
        }
    }
    
    private function generateCertificate($enrollment)
    {
        $existingCertificate = Certificate::where('enrollment_id', $enrollment->id)->first();
        
        if (!$existingCertificate) {
            $certificateNumber = 'EDM-' . strtoupper(uniqid()) . '-' . $enrollment->id;
            
            $certificate = Certificate::create([
                'enrollment_id' => $enrollment->id,
                'certificate_number' => $certificateNumber,
                'issued_at' => now(),
                'pdf_url' => null, // Job will fill this
            ]);
            
            dispatch(new GenerateCertificate($certificate));
        }
    }
}