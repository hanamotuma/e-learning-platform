<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\Quiz;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'course_id';
    
    protected $fillable = [
        'title',
        'description',
        'what_you_will_learn',
        'requirements',
        'image',
        'video_url',
        'price',
        'category_id',
        'instructor_id',
        'difficulty_level',
        'duration_weeks',
        'is_published',
        'published_at',
        'slug',
        'updated_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            $course->slug = \Str::slug($course->title);
        });

        
    }

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'category_id');
    }

    public function sections()
    {
        return $this->hasMany('App\Models\Section', 'course_id', 'course_id')->orderBy('order_position');
    }

    public function enrollments()
    {
        return $this->hasMany('App\Models\Enrollment', 'course_id', 'course_id');
    }

    public function enrolledStudents()
    {
        return $this->belongsToMany('App\Models\User', 'enrollments', 'course_id', 'user_id', 'course_id', 'user_id')
                    ->withPivot('status', 'progress_percentage');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review', 'course_id', 'course_id');
    }

    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'course_id', 'course_id');
    }

    // Accessors
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->where('status', '!=', 'dropped')->count();
    }

    public function getTotalLessonsAttribute()
    {
        return $this->sections->sum(function($section) {
            return $section->lessons->count();
        });
    }
}