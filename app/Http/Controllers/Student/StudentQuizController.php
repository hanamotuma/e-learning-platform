<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StudentQuizController extends Controller
{
    /**
     * Start the quiz and create an attempt record
     */
    public function start(Quiz $quiz)
    {
        // Check if there is already an unfinished attempt
        $attempt = QuizAttempt::firstOrCreate(
            [
                'quiz_id' => $quiz->id,
                'user_id' => auth()->id(),
                'completed_at' => null
            ],
            [
                'started_at' => now(),
                'answers' => [],
            ]
        );

        return Inertia::render('Student/Quizzes/Take', [
            'quiz' => $quiz->load('questions'),
            'attempt' => $attempt
        ]);
    }

    /**
     * Submit the quiz and calculate score
     */
    public function submit(Request $request, QuizAttempt $attempt)
    {
        $quiz = $attempt->quiz->load('questions');
        $submittedAnswers = $request->input('answers'); // Format: [question_id => [selected_index]]
        
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            
            // Check if answer is correct
            $userAnswer = $submittedAnswers[$question->id] ?? null;
            if ($userAnswer == $question->correct_answer) {
                $earnedPoints += $question->points;
            }
        }

        $scorePercentage = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100 : 0;
        $isPassed = $scorePercentage >= $quiz->passing_score;

        $attempt->update([
            'score' => $earnedPoints,
            'total_points' => $totalPoints,
            'answers' => $submittedAnswers,
            'is_passed' => $isPassed,
            'completed_at' => now()
        ]);

        return redirect()->route('student.courses.show', $quiz->section->course_id)
            ->with('success', $isPassed ? 'Congratulations! You passed.' : 'Quiz completed.');
    }
}