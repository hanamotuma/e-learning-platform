<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'transaction_id', 'amount', 'payment_method', 
        'status', 'payment_data'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}