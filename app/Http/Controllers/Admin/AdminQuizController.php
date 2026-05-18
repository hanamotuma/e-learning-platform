<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminQuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['course', 'course.instructor'])
            ->withCount('questions', 'attempts')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total_quizzes' => Quiz::count(),
            'published_quizzes' => Quiz::where('is_published', true)->count(),
            'draft_quizzes' => Quiz::where('is_published', false)->count(),
            'total_attempts' => QuizAttempt::count(),
            'average_score' => round(QuizAttempt::avg('score') ?? 0, 1),
        ];
        
        return Inertia::render('Admin/Quizzes/Index', [
            'quizzes' => $quizzes,
            'stats' => $stats
        ]);
    }
    
    public function show($id)
    {
        $quiz = Quiz::with(['course', 'course.instructor', 'questions'])
            ->withCount('attempts')
            ->findOrFail($id);
        
        $recentAttempts = QuizAttempt::with('user')
            ->where('quiz_id', $id)
            ->latest()
            ->limit(10)
            ->get();
        
        $passRate = $quiz->attempts()->count() > 0 
            ? round(($quiz->attempts()->where('is_passed', true)->count() / $quiz->attempts()->count()) * 100, 1)
            : 0;
        
        return Inertia::render('Admin/Quizzes/Show', [
            'quiz' => $quiz,
            'recentAttempts' => $recentAttempts,
            'passRate' => $passRate
        ]);
    }
    
    public function edit($id)
    {
        $quiz = Quiz::with(['course', 'questions'])->findOrFail($id);
        $courses = Course::with('instructor')->get();
        
        return Inertia::render('Admin/Quizzes/Edit', [
            'quiz' => $quiz,
            'courses' => $courses
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'is_published' => 'boolean'
        ]);
        
        $quiz->update($request->all());
        
        return redirect()->route('admin.quizzes.show', $quiz->id)->with('success', 'Quiz updated successfully');
    }
    
    public function togglePublish($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update(['is_published' => !$quiz->is_published]);
        
        $status = $quiz->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Quiz {$status} successfully");
    }
    
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();
        
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully');
    }
    
    public function questions($quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        
        return Inertia::render('Admin/Quizzes/Questions', [
            'quiz' => $quiz
        ]);
    }
    
    public function addQuestion(Request $request, $quizId)
    {
        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array',
            'options.*' => 'required|string',
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
    
    public function updateQuestion(Request $request, $quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
        
        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,multiple_choice|array',
            'options.*' => 'required|string',
            'correct_answer' => 'required|string'
        ]);
        
        $question->update($request->all());
        
        return response()->json(['success' => true]);
    }
    
    public function deleteQuestion($quizId, $questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function attempts($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $attempts = QuizAttempt::with('user')
            ->where('quiz_id', $quizId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return Inertia::render('Admin/Quizzes/Attempts', [
            'quiz' => $quiz,
            'attempts' => $attempts
        ]);
    }
    
    public function viewAttempt($quizId, $attemptId)
    {
        $attempt = QuizAttempt::with(['user', 'quiz.questions'])
            ->findOrFail($attemptId);
        
        return Inertia::render('Admin/Quizzes/ViewAttempt', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz
        ]);
    }

    public function storeQuiz(Request $request, $courseId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'time_limit_minutes' => 'nullable|integer',
        'passing_score' => 'required|integer|min:0|max:100',
        'max_attempts' => 'required|integer|min:1',
        'is_published' => 'boolean'
    ]);

    $quiz = Quiz::create([
        'title' => $request->title,
        'description' => $request->description,
        'course_id' => $courseId,
        'time_limit_minutes' => $request->time_limit_minutes,
        'passing_score' => $request->passing_score,
        'max_attempts' => $request->max_attempts,
        'is_published' => $request->is_published ?? false
    ]);

    return response()->json(['success' => true, 'quiz' => $quiz]);
}

public function updateQuiz(Request $request, $courseId, $quizId)
{
    $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
    
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'time_limit_minutes' => 'nullable|integer',
        'passing_score' => 'required|integer|min:0|max:100',
        'max_attempts' => 'required|integer|min:1',
        'is_published' => 'boolean'
    ]);

    $quiz->update($request->all());

    return response()->json(['success' => true]);
}

public function deleteQuiz($courseId, $quizId)
{
    $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
    $quiz->delete();

    return response()->json(['success' => true]);
}

public function toggleQuizPublish($courseId, $quizId)
{
    $quiz = Quiz::where('course_id', $courseId)->findOrFail($quizId);
    $quiz->update(['is_published' => !$quiz->is_published]);

    return response()->json(['success' => true]);
}
}