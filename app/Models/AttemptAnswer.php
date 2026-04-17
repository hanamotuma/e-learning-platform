<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'student_quiz_attempt_id',
        'question_id',
        'option_id',
        'answer_text',
        'is_correct',
        'marks_obtained'
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'student_quiz_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuizOption::class, 'option_id');
    }
}