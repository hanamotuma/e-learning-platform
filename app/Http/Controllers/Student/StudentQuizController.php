<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentQuizController extends Controller
{
    public function show($courseId, $quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        
        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'You must be enrolled to take this quiz');
        }
        
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quizId)
            ->get();
        
        $hasPassed = $attempts->contains('is_passed', true);
        $attemptsCount = $attempts->count();
        $canAttempt = $attemptsCount < $quiz->max_attempts && !$hasPassed;
        
        return Inertia::render('Student/Quizzes/Show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'hasPassed' => $hasPassed,
            'canAttempt' => $canAttempt,
            'remainingAttempts' => max(0, $quiz->max_attempts - $attemptsCount)
        ]);
    }

    public function start($courseId, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        
        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'You must be enrolled to take this quiz');
        }
        
        $hasPassed = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quizId)
            ->where('is_passed', true)
            ->exists();
        
        if ($hasPassed) {
            return redirect()->back()->with('error', 'You have already passed this quiz');
        }
        
        $attemptsCount = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quizId)
            ->count();
        
        if ($attemptsCount >= $quiz->max_attempts) {
            return redirect()->back()->with('error', 'You have reached the maximum number of attempts');
        }
        
        $attempt = QuizAttempt::create([
            'quiz_id' => $quizId,
            'user_id' => Auth::id(),
            'started_at' => now(),
        ]);
        
        return redirect()->route('student.quizzes.take', [$courseId, $quizId, $attempt->id]);
    }

    public function take($courseId, $quizId, $attemptId)
    {
        $attempt = QuizAttempt::with('quiz.questions')->findOrFail($attemptId);
        
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($attempt->completed_at) {
            return redirect()->route('student.quizzes.show', [$courseId, $quizId]);
        }
        
        return Inertia::render('Student/Quizzes/Take', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz
        ]);
    }

    public function submit(Request $request, $courseId, $quizId, $attemptId)
    {
        $attempt = QuizAttempt::with('quiz.questions')->findOrFail($attemptId);
        
        if ($attempt->completed_at) {
            return redirect()->route('student.quizzes.results', [$courseId, $quizId, $attemptId]);
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
            $isCorrect = $userAnswer && $userAnswer == $question->correct_answer;
            
            if ($isCorrect) {
                $score += $question->points;
            }
            
            $results[$question->id] = [
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'correct_answer' => $question->correct_answer,
                'points' => $question->points,
                'explanation' => $question->explanation
            ];
        }
        
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;
        $isPassed = $percentage >= $quiz->passing_score;
        
        $attempt->update([
            'score' => round($percentage, 2),
            'total_points' => $totalPoints,
            'answers' => $results,
            'is_passed' => $isPassed,
            'completed_at' => now(),
        ]);
        
        return redirect()->route('student.quizzes.results', [$courseId, $quizId, $attemptId]);
    }

    public function results($courseId, $quizId, $attemptId)
    {
        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
        
        return Inertia::render('Student/Quizzes/Results', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz
        ]);
    }
}