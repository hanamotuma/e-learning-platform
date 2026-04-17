<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chatbot_session_id',
        'sender',
        'message',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(ChatbotSession::class);
    }
}