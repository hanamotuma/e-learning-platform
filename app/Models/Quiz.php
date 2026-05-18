<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title', 'description', 'course_id', 'section_id',
        'time_limit_minutes', 'passing_score', 'max_attempts', 'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'time_limit_minutes' => 'integer',
        'passing_score' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}