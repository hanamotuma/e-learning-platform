<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseCreatedNotification extends Notification
{
    use Queueable;

    protected $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Course Created Successfully!',
            'message' => "Your course '{$this->course->title}' has been created successfully.",
            'type' => 'course_update',
            'course_id' => $this->course->id,
            'action_url' => route('instructor.courses.edit', $this->course->id)
        ];
    }
}