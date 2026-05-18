<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id', 'question_text', 'type', 'points',
        'options', 'correct_answer', 'explanation'
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}