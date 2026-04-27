<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'certificate_number',
        'certificate_url',
        'issued_at',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function enrollment()
    {
return $this->belongsTo(Enrollment::class, 'enrollment_id', 'id');    }
}