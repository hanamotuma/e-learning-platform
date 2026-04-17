<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'conversation_history',
        'status'
    ];

    protected $casts = [
        'conversation_history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}