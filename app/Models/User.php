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

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username', 'email', 'password', 'bio', 'profile_picture_url', 'is_active', 'full_name',
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
}