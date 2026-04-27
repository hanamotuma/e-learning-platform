<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    // REMOVED: quiz_id (Keep it default 'id' to match the migration)
    // If you REALLY want 'quiz_id', change the migration to $table->id('quiz_id')

    protected $fillable = [
        'course_id',
                'section_id', // Added
        'lesson_id',
        'title',
        'description',
        'time_limit_minutes',
        'passing_score',
        'is_published', // Added to match migration
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'time_limit_minutes' => 'integer',
        'passing_score' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class , 'section_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}