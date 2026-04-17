<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'option_id',
        'correct_text'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuizOption::class, 'option_id');
    }
}