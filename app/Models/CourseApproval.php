<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseApproval extends Model
{
    protected $fillable = [
        'course_id',
        'admin_id',
        'status',
        'comments',
        'reviewed_at',
    ];
    
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];
    
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}