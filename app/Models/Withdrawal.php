<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_method',
        'account_details',
        'approved_at',
        'paid_at',
        'rejection_reason',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'account_details' => 'array',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}