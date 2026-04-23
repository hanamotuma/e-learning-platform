<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Section $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'nullable|file|mimes:mp4,mov,avi',
            'content' => 'nullable|string',
        ]);

        // upload video
        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('lessons', 'public');
        }

        // safe ordering
        $nextOrder = $section->lessons()->max('order_position') + 1;

        $section->lessons()->create([
            'title' => $request->title,
            'video_url' => $videoPath,
            'content' => $request->content ?? 'No content yet',
            'order_position' => $nextOrder,
        ]);

        return back();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return back();
    }
}