<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $table = 'options'; // Ensure this matches your migration
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}