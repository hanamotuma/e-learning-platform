<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; // ✅ FIX HERE

use App\Models\ProgressTracking;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressTrackingController extends Controller
{
    // MARK LESSON AS COMPLETED / UPDATE PROGRESS
    public function update(Request $request, Lesson $lesson)
    {
        $user = Auth::user();

        $request->validate([
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|in:not_started,in_progress,completed',
        ]);

        $progress = ProgressTracking::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $lesson->section->course_id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => $request->status ?? 'in_progress',
                'progress_percentage' => $request->progress_percentage ?? 0,
                'last_accessed_at' => now(),
                'completed_at' => $request->status === 'completed' ? now() : null,
            ]
        );

        return back();
    }

    // GET USER COURSE PROGRESS
    public function courseProgress($courseId)
    {
        $user = Auth::user();

        return ProgressTracking::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->get();
    }
}