<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    /**
     * Store a new quiz for a section
     */
    public function store(Request $request, Section $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'time_limit_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        $section->quizzes()->create([
            'course_id'          => $section->course_id, 
            'title'              => $validated['title'],
            'description'        => $validated['description'],
            'time_limit_minutes' => $validated['time_limit_minutes'],
            'passing_score'      => $validated['passing_score'],
            'is_published'       => false, 
        ]);

        return back()->with('success', 'Quiz created successfully!');
    }

    /**
     * Show the quiz management page
     */
    public function show(Quiz $quiz)
    {
        // IMPORTANT: Load 'questions.quizOptions' so the frontend can see the options
        return Inertia::render('Admin/Quizzes/Show', [
            'quiz' => $quiz->load([
                'questions.quizOptions', // Relational options for each question
                'section.course'
            ]),
        ]);
    }

    /**
     * Update quiz settings
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'time_limit_minutes' => 'required|integer',
            'passing_score'      => 'required|integer',
            'is_published'       => 'boolean'
        ]);

        $quiz->update($validated);

        return back()->with('success', 'Quiz updated.');
    }

    /**
     * Delete a quiz
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return back()->with('success', 'Quiz deleted.');
    }
}