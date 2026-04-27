<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt; // Assuming this is your attempt model name
use App\Models\AttemptAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttemptReviewController extends Controller
{
    /**
     * Display the specific quiz attempt for review
     */
    public function show($attemptId)
    {
        // Load the attempt with the student, the quiz, and all answers
        // We also load the questions and their options to show 'what was correct' vs 'what was picked'
        $attempt = QuizAttempt::with([
            'student', 
            'quiz', 
            'answers.question.quizOptions', 
            'answers.option'
        ])->findOrFail($attemptId);

        return Inertia::render('Admin/Attempts/Review', [
            'attempt' => $attempt,
        ]);
    }

    /**
     * Optional: Update marks manually (for short answers)
     */
    public function updateScore(Request $request, AttemptAnswer $answer)
    {
        $validated = $request->validate([
            'marks_obtained' => 'required|numeric|min:0',
            'is_correct' => 'required|boolean'
        ]);

        $answer->update($validated);

        return back()->with('success', 'Score updated.');
    }
}