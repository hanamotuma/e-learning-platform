<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    protected $details;

    // We pass an array of dynamic details here
    public function __construct($details)
    {
        $this->details = $details;
    }

    // Tell Laravel to save this in your 'notifications' table
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->details['type'],
            'title' => $this->details['title'],
            'message' => $this->details['message'],
            'action_url' => $this->details['action_url'] ?? null,
        ];
    }
}