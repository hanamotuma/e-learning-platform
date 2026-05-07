<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    // Standard primary key is 'id'
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'course_id',
        'order_position',
        'title',
        'description',
    ];

    public function course()
{
    return $this->belongsTo(Course::class);
}

   public function lessons()
{
    return $this->hasMany(Lesson::class)->orderBy('order');
}

   public function quizzes()
{
    return $this->hasMany(Quiz::class);
}
}