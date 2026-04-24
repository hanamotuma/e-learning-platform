<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * STORE SECTION
     */
        public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Get next order safely
        $lastOrder = Section::where('course_id', $course->id)
            ->max('order_position');

        Section::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'order_position' => ($lastOrder ?? 0) + 1, // ✅ FIXED
        ]);

        return back();
    }

    /**
     * UPDATE SECTION
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $section->update([
            'title' => $request->title,
        ]);

        return back();
    }

    /**
     * DELETE SECTION
     */
    public function destroy(Section $section)
    {
        // ✅ Delete lessons first (IMPORTANT)
        $section->lessons()->delete();

        $courseId = $section->course_id;

        $section->delete();

        // ✅ Reorder remaining sections
        $sections = Section::where('course_id', $courseId)
            ->orderBy('order_position')
            ->get();

        foreach ($sections as $index => $sec) {
            $sec->update([
                'order_position' => $index + 1
            ]);
        }

        return back();
    }

}