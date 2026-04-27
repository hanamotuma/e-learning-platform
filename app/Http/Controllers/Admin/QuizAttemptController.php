<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt; // Replace with your actual model name
use Inertia\Inertia;

class QuizAttemptController extends Controller
{
    public function index()
    {
        $attempts = QuizAttempt::with(['user', 'quiz.section.course'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Quizzes/Attempts/Index', [
            'attempts' => $attempts
        ]);
    }

    public function show($id)
    {
        // This is the one you said is already working
        return Inertia::render('Admin/Quizzes/Attempts/Show', [
            'attempt' => QuizAttempt::with(['user', 'quiz', 'answers.question'])->findOrFail($id)
        ]);
    }
}