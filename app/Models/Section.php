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
    ];

    public function course()
    {
        // Use the default keys unless your Course model uses something else as PK
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'section_id', 'id')
            ->orderBy('order_position');
    }

    public function quizzes()
    {
        // FIX: The local key should be 'id' (the primary key of this Section)
        return $this->hasMany(Quiz::class, 'section_id', 'id');
    }
}