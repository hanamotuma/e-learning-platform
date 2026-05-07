<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;
    

   protected $fillable = [
    'title',
    'slug',
    'description',
    'price',
    'instructor_id', 
    'category_id',
    'difficulty_level',
    'status',
    'instructor_bio',
    'rating',
    'reviews_count',
    'image_url',
    'duration_hours',
    'is_published',
    'curriculum',
    'requirements',
    'learning_outcomes',
 ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_published' => 'boolean',
        'duration_weeks' => 'integer',
        'curriculum' => 'array',
        'requirements' => 'array',
        'learning_outcomes' => 'array',
            ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
 * Get the route key for the model.
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'slug';
}

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_position');
    }

    public function lessons(): HasMany
    {
        return $this->hasManyThrough(Lesson::class, Section::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot('status', 'enrolled_at')
            ->wherePivot('status', 'active');
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function quizzes()
{
    return $this->hasMany(Quiz::class);
}
    public function getFinalPriceAttribute()
    {
        // Placeholder for discount logic
        return $this->price;
    }
    public function getIsEnrolledByCurrentUserAttribute(){

    if (!auth()->check()) return false;
    return $this->students()->where('user_id', auth()->id())->exists();

    }

    // Helper methods
    public function isUserEnrolled($userId)
    {
        return $this->enrollments()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
}