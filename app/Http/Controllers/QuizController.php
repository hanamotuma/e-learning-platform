<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\Option;
use App\Models\Enrollment;
use App\Models\ProgressTracking;
use App\Models\Certificate;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    public function show($id)
    {
        $quiz = Quiz::with(['section', 'course', 'questions.options'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()->route('courses.show', $quiz->course->slug)
                ->with('error', 'You need to enroll in this course to take the quiz.');
        }
        
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $hasPassed = $attempts->contains('is_passed', true);
        $attemptsCount = $attempts->count();
        $canAttempt = $attemptsCount < $quiz->max_attempts && !$hasPassed;
        
        // Get completed lessons in this section to unlock quiz
        $completedLessonsInSection = ProgressTracking::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->whereIn('lesson_id', $quiz->section->lessons->pluck('id'))
            ->where('status', 'completed')
            ->count();
        
        $totalLessonsInSection = $quiz->section->lessons->count();
        $isUnlocked = $totalLessonsInSection === 0 || $completedLessonsInSection >= $totalLessonsInSection;
        
        return Inertia::render('Quizzes/Show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'hasPassed' => $hasPassed,
            'canAttempt' => $canAttempt && $isUnlocked,
            'remainingAttempts' => max(0, $quiz->max_attempts - $attemptsCount),
            'isUnlocked' => $isUnlocked,
            'requiredLessons' => $totalLessonsInSection,
            'completedLessons' => $completedLessonsInSection,
        ]);
    }
    
    public function start($id)
    {
        $quiz = Quiz::findOrFail($id);
        $user = Auth::user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('status', 'active')
            ->first();
        
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
        
        if ($attemptsCount >= $quiz->max_attempts) {
            return redirect()->back()->with('error', 'You have reached the maximum number of attempts.');
        }
        
        $attempt = QuizAttempt::create([
            'quiz_id' => $id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        
        return redirect()->route('quizzes.take', $attempt->id);
    }
    
    public function take($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.questions.options', 'quiz.course'])
            ->findOrFail($attemptId);
        
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($attempt->completed_at) {
            return redirect()->route('quizzes.show', $attempt->quiz_id)
                ->with('info', 'This quiz attempt has already been completed.');
        }
        
        $quiz = $attempt->quiz;
        $questions = $quiz->questions->shuffle();
        
        // Decode existing answers if any
        $savedAnswers = $attempt->answers ? json_decode($attempt->answers, true) : [];
        
        return Inertia::render('Quizzes/Take', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'questions' => $questions,
            'savedAnswers' => $savedAnswers,
            'totalQuestions' => $questions->count(),
        ]);
    }
    
    public function submit($attemptId, Request $request)
    {
        $attempt = QuizAttempt::with(['quiz.questions.options', 'quiz.course'])
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
            $totalPoints += $question->marks;
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $this->checkAnswer($question, $userAnswer);
            
            if ($isCorrect) {
                $score += $question->marks;
            }
            
            $results[$question->id] = [
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'correct_answer' => $this->getCorrectAnswer($question),
                'points' => $question->marks,
                'explanation' => $question->explanation ?? 'No explanation provided.',
            ];
        }
        
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;
        $isPassed = $percentage >= $quiz->passing_score;
        
        $attempt->update([
            'score' => round($percentage, 2),
            'answers' => $results,
            'is_passed' => $isPassed,
            'completed_at' => now(),
        ]);
        
        // Update course progress
        $this->updateCourseProgress($attempt->user_id, $quiz->course_id);
        
        // Notify student of result
        Notification::create([
            'user_id' => $attempt->user_id,
            'type' => $isPassed ? 'quiz_passed' : 'quiz_failed',
            'title' => $isPassed ? '🎉 Quiz Passed!' : '📝 Quiz Completed',
            'message' => $isPassed 
                ? "You passed \"{$quiz->title}\" with {$percentage}%! Great job!" 
                : "You scored {$percentage}% on \"{$quiz->title}\". Please review and try again.",
            'action_url' => route('quizzes.results', $attempt->id),
        ]);
        
        return redirect()->route('quizzes.results', $attempt->id);
    }
    
    public function saveProgress($attemptId, Request $request)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (!$attempt->completed_at) {
            $attempt->update([
                'answers' => json_encode($request->input('answers', [])),
            ]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function results($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.course', 'quiz.questions'])
            ->findOrFail($attemptId);
        
        if (!$attempt->completed_at) {
            return redirect()->route('quizzes.take', $attemptId);
        }
        
        $results = $attempt->answers ? json_decode($attempt->answers, true) : [];
        
        return Inertia::render('Quizzes/Results', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'results' => $results,
            'score' => $attempt->score,
            'isPassed' => $attempt->is_passed,
        ]);
    }
    
    private function checkAnswer($question, $userAnswer)
    {
        if (is_null($userAnswer)) return false;
        
        switch ($question->type) {
            case 'mcq':
                $correctOption = $question->options->where('is_correct', true)->first();
                return $correctOption && $correctOption->id == $userAnswer;
                
            case 'true_false':
                $correctOption = $question->options->where('is_correct', true)->first();
                return $correctOption && $correctOption->option_text == $userAnswer;
                
            case 'short_answer':
                $adminAnswer = \App\Models\AdminAnswer::where('question_id', $question->id)->first();
                if ($adminAnswer && $adminAnswer->correct_text) {
                    return strtolower(trim($userAnswer)) === strtolower(trim($adminAnswer->correct_text));
                }
                return false;
                
            default:
                return false;
        }
    }
    
    private function getCorrectAnswer($question)
    {
        switch ($question->type) {
            case 'mcq':
                $correct = $question->options->where('is_correct', true)->first();
                return $correct ? $correct->option_text : 'No correct answer set';
                
            case 'true_false':
                return 'True or False answer';
                
            case 'short_answer':
                $adminAnswer = \App\Models\AdminAnswer::where('question_id', $question->id)->first();
                return $adminAnswer ? $adminAnswer->correct_text : 'No answer key';
                
            default:
                return 'Answer key not available';
        }
    }
    
    private function updateCourseProgress($userId, $courseId)
    {
        $course = \App\Models\Course::with(['sections.lessons', 'sections.quizzes'])->find($courseId);
        $enrollment = Enrollment::where('user_id', $userId)->where('course_id', $courseId)->first();
        
        if (!$enrollment) return;
        
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
        
        if ($progressPercentage >= 100 && !$enrollment->certificate_issued) {
            $this->generateCertificate($enrollment);
        }
    }
    
    private function generateCertificate($enrollment)
    {
        $existingCertificate = Certificate::where('user_id', $enrollment->user_id)
            ->where('course_id', $enrollment->course_id)
            ->first();
        
        if (!$existingCertificate) {
            $certificateNumber = 'CERT-' . strtoupper(uniqid()) . '-' . $enrollment->id;
            
            Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'certificate_number' => $certificateNumber,
                'issued_at' => now(),
            ]);
            
            $enrollment->update(['certificate_issued' => true]);
            
            Notification::create([
                'user_id' => $enrollment->user_id,
                'type' => 'certificate_earned',
                'title' => '🎓 Certificate Earned!',
                'message' => 'Congratulations! You\'ve completed the course. Your certificate is ready.',
                'action_url' => route('certificates.index'),
            ]);
        }
    }
}