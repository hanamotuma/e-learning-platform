<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    

    protected $fillable = [
        'quiz_id',
        'question_text',
        'type',
        'marks',
        'options',
        'question_type', 
        'correct_answer',
        'points',        
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

    public function options()
{
    return $this->hasMany(Option::class);
}


public function adminAnswers()
{
    return $this->hasMany(AdminAnswer::class);
}
}