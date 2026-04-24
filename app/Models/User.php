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
use Spatie\Permission\Models\HasRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

   protected $fillable = [
    'username', 'email', 'password', 'bio', 'profile_picture_url', 'is_active', 'name',
    'address', 'city', 'state', 'zip_code', 'country', 'phone', 'interests', 'education'
];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

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
   public function courses(): HasMany
{
    // If they are in the same folder, you can use the string name
    return $this->hasMany('App\Models\Course');
}

    public function enrollments(): HasMany
    {
        return $this->hasMany('App\Models\Enrollment');
    }

    public function payments(): HasMany
    {
        return $this->hasMany('App\Models\Payment');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany('App\Models\Review');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany('App\Models\Notification');
    }
    public function unreadNotifications()
{
    return $this->hasMany(Notification::class)->whereNull('read_at');
}
}