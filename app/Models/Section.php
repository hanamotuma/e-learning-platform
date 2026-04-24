<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;

class Section extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // 🔥 CHANGE THIS

    protected $fillable = [
        'course_id',
        'order_position',
        'title',
    ];

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id', 'course_id');
    }


public function lessons()
{
    return $this->hasMany(Lesson::class, 'section_id', 'id')
        ->orderBy('order_position');
}
    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'section_id', 'section_id');
    }
}