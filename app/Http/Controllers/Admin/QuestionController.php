<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // <--- ADD THIS LINE
use App\Models\AdminAnswer;

class QuestionController extends Controller
{
   public function store(Request $request, Quiz $quiz)
{
    $validated = $request->validate([
        'question_text' => 'required|string',
        'question_type' => 'required|string|in:mcq,true_false,short_answer',
        'points' => 'required|integer|min:1',
        'options' => 'required_unless:question_type,short_answer|array',
        'options.*.option_text' => 'required|string',
        'options.*.is_correct' => 'required|boolean',
        'correct_text' => 'required_if:question_type,short_answer|nullable|string',
    ]);

    return DB::transaction(function () use ($quiz, $validated, $request) {
        // 1. Save the Question
        $question = $quiz->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'],
        ]);

        // 2. Handle MCQ / True-False
        if ($validated['question_type'] !== 'short_answer') {
            foreach ($validated['options'] as $optionData) {
                // Save to options table
                $option = $question->quizOptions()->create([
                    'option_text' => $optionData['option_text'],
                ]);

                // 3. IF this option is correct, create the AdminAnswer record
                if ($optionData['is_correct']) {
                    $question->adminAnswers()->create([
                        'option_id' => $option->id,
                    ]);
                }
            }
        } 
        // 4. Handle Short Answer
        else {
            $question->adminAnswers()->create([
                'correct_text' => $validated['correct_text'],
            ]);
        }

        return back()->with('success', 'Question and Answer stored!');
    });
}

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }
}