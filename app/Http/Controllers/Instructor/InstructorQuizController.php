<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstructorQuizController extends Controller
{
    public function index($courseId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        $quizzes = Quiz::where('course_id', $courseId)->withCount('questions')->get();
        
        return Inertia::render('Instructor/Quizzes/Index', [
            'course' => $course,
            'quizzes' => $quizzes
        ]);
    }

    public function create($courseId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        return Inertia::render('Instructor/Quizzes/Create', [
            'course' => $course
        ]);
    }

    public function store(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1'
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $courseId,
            'time_limit_minutes' => $request->time_limit_minutes,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts,
            'is_published' => false
        ]);

        return redirect()->route('instructor.quizzes.edit', [$courseId, $quiz->id]);
    }

    public function edit($courseId, $quizId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        
        return Inertia::render('Instructor/Quizzes/Edit', [
            'course' => $course,
            'quiz' => $quiz
        ]);
    }

    public function update(Request $request, $courseId, $quizId)
    {
        $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1'
        ]);

        $quiz->update($request->all());

        return redirect()->back()->with('success', 'Quiz updated successfully');
    }

    public function addQuestion(Request $request, $courseId, $quizId)
    {
        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array',
            'correct_answer' => 'required|string'
        ]);

        $question = Question::create([
            'quiz_id' => $quizId,
            'question_text' => $request->question_text,
            'type' => $request->type,
            'points' => $request->points,
            'options' => $request->type === 'multiple_choice' ? $request->options : null,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation
        ]);

        return response()->json(['success' => true, 'question' => $question]);
    }

    public function updateQuestion(Request $request, $courseId, $quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
        
        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array',
            'correct_answer' => 'required|string'
        ]);

        $question->update($request->all());

        return response()->json(['success' => true]);
    }

    public function deleteQuestion($courseId, $quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->delete();

        return response()->json(['success' => true]);
    }

    public function publish($courseId, $quizId)
    {
        $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
        
        if ($quiz->questions()->count() === 0) {
            return redirect()->back()->with('error', 'Add at least one question before publishing');
        }
        
        $quiz->update(['is_published' => true]);

        return redirect()->back()->with('success', 'Quiz published successfully');
    }

    public function destroy($courseId, $quizId)
    {
        $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
        $quiz->delete();

        return redirect()->route('instructor.quizzes.index', $courseId);
    }
}