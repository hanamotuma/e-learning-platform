<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'user_id', 'course_id', 'progress', 'completed', 
        'enrolled_at', 'payment_status', 'amount_paid',
        'transaction_id', 'payment_method'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'progress' => 'integer',
        'completed' => 'boolean',
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