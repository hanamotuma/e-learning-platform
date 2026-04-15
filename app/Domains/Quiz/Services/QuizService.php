<?php

namespace App\Domains\Quiz\Services;

use App\Domains\Quiz\Models\QuizAttempt;
use App\Domains\Quiz\Models\UserAnswer;
use Illuminate\Support\Facades\DB;

class QuizService
{
    public function startAttempt($user, $quiz)
    {
        $attemptNumber = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count() + 1;

        return QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function submitAttempt($attemptId, $answers)
    {
        $attempt = QuizAttempt::with('quiz.questions.answers')->findOrFail($attemptId);
        
        DB::transaction(function () use ($attempt, $answers) {
            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($answers as $questionId => $answerData) {
                $question = $attempt->quiz->questions->find($questionId);
                if (!$question) continue;

                $totalPoints += $question->points;
                $isCorrect = false;
                $pointsEarned = 0;

                if ($question->type === 'essay') {
                    // Store essay answer for manual grading
                    UserAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'essay_answer' => $answerData['answer'],
                        'points_earned' => 0
                    ]);
                } else {
                    $selectedAnswer = $question->answers->find($answerData['answer_id']);
                    if ($selectedAnswer) {
                        $isCorrect = $selectedAnswer->is_correct;
                        $pointsEarned = $isCorrect ? $question->points : 0;
                        $earnedPoints += $pointsEarned;

                        UserAnswer::create([
                            'quiz_attempt_id' => $attempt->id,
                            'question_id' => $questionId,
                            'answer_id' => $selectedAnswer->id,
                            'is_correct' => $isCorrect,
                            'points_earned' => $pointsEarned
                        ]);
                    }
                }
            }

            $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            
            $attempt->update([
                'score' => $score,
                'status' => 'completed',
                'completed_at' => now()
            ]);
        });

        return $attempt->fresh();
    }
}