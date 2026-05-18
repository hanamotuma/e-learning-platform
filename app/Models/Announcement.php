<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'target_audience',
        'send_email',
        'send_notification',
        'publish_at',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_notification' => 'boolean',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime'
    ];
}