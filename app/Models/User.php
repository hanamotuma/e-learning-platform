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
use App\Models\Activity;
use Spatie\Permission\Models\HasRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

  protected $fillable = [
     'name',
        'username',
        'email',
        'password',
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
        'role',
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
    public function getprofilePictureUrlAttribute($value)
    {
       if ($value) {
        return $value;
    } else {
        return null;
       }
    }

    // Accessor for name attribute (for backward compatibility)
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    // Mutator for name attribute
    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
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
    

    // Added : HasMany return types to satisfy the IDE
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('progress', 'completed', 'enrolled_at')
                    ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }
  public function enrolledCourses()
{
    return $this->belongsToMany(Course::class, 'course_user')
                ->withPivot('enrolled_at', 'payment_status', 'amount_paid', 'payment_method')
                ->withTimestamps();
}
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function isEnrolledIn($courseId)
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function notifications()
{
    return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
}

public function unreadNotifications()
{
    return $this->notifications()->whereNull('read_at');
}
public function certificates()
{
    return $this->hasMany(Certificate::class);
}
public function upcomingActivities()
{
    return $this->hasMany(Activity::class)
                ->where('scheduled_at', '>', now())
                ->orderBy('scheduled_at', 'asc');
}
}