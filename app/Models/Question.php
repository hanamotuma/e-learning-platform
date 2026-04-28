<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    // Remove $primaryKey if your migration uses $table->id()
    // protected $primaryKey = 'question_id'; 

    protected $fillable = [
        'quiz_id',
        'question_text',
        'options',
        'question_type', // Must match migration column name
        'correct_answer',
        'points',        // Must match migration column name
        'explanation',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

public function quizOptions() {
    return $this->hasMany(QuizOption::class, 'question_id');
}
public function adminAnswers()
{
    return $this->hasMany(AdminAnswer::class);
}
}