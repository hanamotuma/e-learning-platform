<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'min_purchase',
        'starts_at',
        'expires_at',
        'is_active',
        'instructor_id',
    ];
    
    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}