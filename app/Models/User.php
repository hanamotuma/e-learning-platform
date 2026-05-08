<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Certificate;
use App\Models\ProgressTracking;
use App\Models\Notification;
use App\Models\QuizAttempts;
use App\Models\SupportTicket;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'is_active',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'bio',
        'interests',
        'education',
        'profile_picture_url',
        'telebirr_phone',
        'cbe_phone',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $primaryKey = 'user_id';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function getProfilePictureUrlAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            return null;
        }
    }

    // FIXED: Accessor for full_name attribute
    public function getFullNameAttribute()
    {
        return $this->attributes['full_name'] ?? 'Student';
    }

    // FIXED: Mutator for full_name attribute
    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
    }

    // Name accessor for compatibility
    public function getNameAttribute()
    {
        return $this->attributes['full_name'] ?? 'Student';
    }

    public function redirectRoute()
    {
        if ($this->hasRole('admin')) {
            return '/admin/dashboard';
        }
        
        if ($this->hasRole('instructor')) {
            return '/instructor/dashboard';
        }
        
        if ($this->hasRole('student')) {
            return '/student/dashboard';
        }
        
        return '/';
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
                    ->withPivot('progress_percentage', 'status', 'enrolled_at', 'completed_at')
                    ->withTimestamps();
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function isEnrolledIn($courseId)
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function progressTracking()
    {
        return $this->hasMany(ProgressTracking::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->where('is_read', false);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'user_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempts::class, 'user_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }
}