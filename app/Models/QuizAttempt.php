<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    // 🔥 FIXED: Match migration (id) or remove this line entirely
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'total_points', // 🔥 ADDED: Match migration
        'answers',
        'is_passed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'is_passed' => 'boolean',
        'score' => 'integer', // Match migration (integer)
        'total_points' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function answers() {
    return $this->hasMany(AttemptAnswer::class, 'student_quiz_attempt_id');
}


}