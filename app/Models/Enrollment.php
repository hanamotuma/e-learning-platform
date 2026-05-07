<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = ['user_id', 'course_id', 'status', 'enrolled_at', 'completed_at', 'amount_paid', 'progress_percentage', 'last_accessed_at', 'certificate_issued', 'certificate_url'];


    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
        'certificate_issued' => 'boolean',
        'amount_paid' => 'decimal:2'
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}