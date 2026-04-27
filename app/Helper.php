<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;


class NotificationHelper
{
    public static function send($userId, $type, $title, $message, $actionUrl = null, $data = [])
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    public static function sendToAllStudents($type, $title, $message, $actionUrl = null)
    {
        $students = User::role('student')->get();
        
        foreach ($students as $student) {
            self::send($student->id, $type, $title, $message, $actionUrl);
        }
    }

    public static function sendToCourseStudents($courseId, $type, $title, $message, $actionUrl = null)
    {
        $students = User::role('student')
            ->whereHas('enrollments', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->get();
        
        foreach ($students as $student) {
            self::send($student->id, $type, $title, $message, $actionUrl);
        }
    }
}