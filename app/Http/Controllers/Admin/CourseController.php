<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgressTracking; // ✅ FIX HERE

class CourseController extends Controller
{
    /**
     * LIST COURSES
     */
    public function index()
    {
        $courses = Course::with(['category', 'instructor'])
            ->latest()
            ->paginate(10)
            ->through(fn ($course) => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'price' => $course->price,
                'is_published' => $course->is_published,
                'image' => $course->image,
                'video_url' => $course->video_url,
                'duration_weeks' => $course->duration_weeks,
                'category' => $course->category?->name,
                'instructor' => $course->instructor?->name,
                'created_at' => $course->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses
        ]);
    }

    /**
     * CREATE
     */
    public function create()
    {
        return Inertia::render('Admin/Courses/Create', [
            'categories' => Category::select('id', 'name')->get()
        ]);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'duration_weeks' => 'nullable|integer',
            'image' => 'nullable|image',
            'video' => 'nullable|file|mimes:mp4,mov,avi',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('courses/images', 'public');
        }

        if ($request->hasFile('video')) {
            $validated['video_url'] = $request->file('video')->store('courses/videos', 'public');
        }

        Course::create([
            ...$validated,
            'instructor_id' => auth()->id(),
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.courses.index');
    }

    /**
     * EDIT
     */
    public function edit(Course $course)
    {
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course->load([
                'sections' => fn ($q) => $q->orderBy('order_position'),
                'sections.lessons' => fn ($q) => $q->orderBy('order_position'),
            ]),
            'categories' => Category::all()
        ]);
    }

    /**
     * SHOW COURSE + PROGRESS (🔥 FIXED HERE)
     */
    public function show(Course $course)
    {
        $course->load([
            'category',
            'sections.lessons.resources',
            'sections.lessons' => fn ($q) => $q->orderBy('order_position'),
        ]);

        // 🔥 ADD USER PROGRESS
        $progress = \App\Models\ProgressTracking::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->get();

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
            'progressData' => $progress
        ]);
    }

    /**
     * DELETE
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return back();
    }
}