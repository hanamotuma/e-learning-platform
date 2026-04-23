<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
                'video_url' => $course->video_url, // ✅ FIXED
                'requirements' => $course->requirements,
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
     * CREATE PAGE
     */
    public function create()
    {
        return Inertia::render('Admin/Courses/Create', [
            'categories' => Category::select('id', 'name')->get()
        ]);
    }

    /**
     * STORE COURSE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'nullable|string',
            'duration_weeks' => 'nullable|integer',
            'image' => 'nullable|image',
            'video' => 'nullable|file|mimes:mp4,mov,avi',
        ]);

        // IMAGE
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('courses/images', 'public');
        }

        // VIDEO
        if ($request->hasFile('video')) {
            $validated['video_url'] = $request->file('video')
                ->store('courses/videos', 'public');
        }

        Course::create([
            ...$validated,
            'instructor_id' => auth()->id(),
            'slug' => Str::slug($validated['title']) . '-' . uniqid(), // ✅ safer
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.courses.index');
    }

    /**
     * EDIT PAGE
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
     * UPDATE COURSE
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'nullable|string',
            'duration_weeks' => 'nullable|integer',
            'image' => 'nullable|image',
            'video' => 'nullable|file|mimes:mp4,mov,avi',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('courses/images', 'public');
        }

        if ($request->hasFile('video')) {
            $validated['video_url'] = $request->file('video')
                ->store('courses/videos', 'public');
        }

        $course->update([
            ...$validated,
            'slug' => Str::slug($validated['title']) . '-' . $course->id,
        ]);

        return redirect()->route('admin.courses.index');
    }

    /**
     * SHOW COURSE (FULL STRUCTURE)
     */
    public function show(Course $course)
    {
        $course->load([
            'category',
            'sections' => fn ($q) => $q->orderBy('order_position'),
            'sections.lessons' => fn ($q) => $q->orderBy('order_position'),
        ]);

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course
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