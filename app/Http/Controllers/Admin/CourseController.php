<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\ProgressTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * LIST COURSES (Admin View)
     */
    public function index(Request $request)
    {
        $query = Course::with(['category', 'instructor']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $courses = $query->latest()->paginate(10);
        
        // Transform data for frontend
        $courses->through(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'price' => (float) $course->price,
                'is_published' => (bool) $course->is_published,
                'image' => $course->image,
                'video_url' => $course->video_url,
                'duration_weeks' => $course->duration_weeks,
                'category' => $course->category?->name,
                'instructor' => $course->instructor?->name,
                'lessons_count' => $course->lessons()->count(),
                'reviews_count' => $course->reviews()->count(),
                'created_at' => $course->created_at?->diffForHumans(),
            ];
        });

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => [
                'search' => $request->search ?? ''
            ]
        ]);
    }

    /**
     * SHOW CREATE COURSE FORM
     */
    public function create()
    {
        return Inertia::render('Admin/Courses/Create', [
            'categories' => Category::select('id', 'name')->get()
        ]);
    }

    /**
     * STORE NEW COURSE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_weeks' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:102400',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('courses/images', 'public');
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $validated['video_url'] = $request->file('video')->store('courses/videos', 'public');
        }

        // Create the course
        $course = Course::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'description' => $validated['description'] ?? null,
            'what_you_will_learn' => $validated['what_you_will_learn'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
            'difficulty_level' => $validated['difficulty_level'] ?? 'beginner',
            'duration_weeks' => $validated['duration_weeks'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
            'instructor_id' => Auth::id(),
            'image' => $validated['image'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully!');
    }

    /**
     * SHOW SINGLE COURSE (Admin View with Progress)
     */
    public function show(Course $course)
    {
        $course->load([
            'category',
            'instructor',
            'sections' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.quizzes',
            'sections.lessons' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.lessons.resources',
        ]);

        // Get user progress for this course
        $progress = ProgressTracking::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->get();

        // Check if current user is enrolled
        $isEnrolled = false;
        $enrollment = null;
        
        if (Auth::check()) {
            $enrollment = Enrollment::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->first();
            $isEnrolled = (bool) $enrollment;
        }

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
            'progressData' => $progress,
            'isEnrolled' => $isEnrolled,
            'enrollment' => $enrollment
        ]);
    }

    /**
     * SHOW EDIT COURSE FORM
     */
    public function edit(Course $course)
    {
        $course->load([
            'sections' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.lessons' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.lessons.resources',
        ]);

        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'categories' => Category::all()
        ]);
    }

    /**
     * UPDATE COURSE
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_weeks' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:102400',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
            $validated['image'] = $request->file('image')->store('courses/images', 'public');
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            // Delete old video if exists
            if ($course->video_url && Storage::disk('public')->exists($course->video_url)) {
                Storage::disk('public')->delete($course->video_url);
            }
            $validated['video_url'] = $request->file('video')->store('courses/videos', 'public');
        }

        // Update slug if title changed
        if (isset($validated['title']) && $validated['title'] !== $course->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        }

        $course->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'course' => $course]);
        }

        return redirect()->back()->with('success', 'Course updated successfully!');
    }

    /**
     * DELETE COURSE
     */
    public function destroy(Course $course)
    {
        // Delete associated images and videos
        if ($course->image && Storage::disk('public')->exists($course->image)) {
            Storage::disk('public')->delete($course->image);
        }
        if ($course->video_url && Storage::disk('public')->exists($course->video_url)) {
            Storage::disk('public')->delete($course->video_url);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully!');
    }

    /**
     * TOGGLE COURSE PUBLISH STATUS
     */
    public function togglePublish(Course $course)
    {
        $course->update([
            'is_published' => !$course->is_published
        ]);

        return response()->json([
            'success' => true,
            'is_published' => $course->is_published
        ]);
    }

    /**
     * STUDENT COURSE VIEW (WITH ENROLLMENT CHECK)
     * This is for the public/student facing course detail page
     */
    public function studentShow($id)
    {
        $course = Course::with([
            'category',
            'instructor',
            'sections' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.lessons' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'sections.quizzes',
        ])->findOrFail($id);

        // Check if user is enrolled
        $isEnrolled = false;
        $enrollment = null;
        
        if (Auth::check()) {
            $enrollment = Enrollment::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->first();
            $isEnrolled = (bool) $enrollment;
        }

        return Inertia::render('Student/CourseDetail', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'what_you_will_learn' => $course->what_you_will_learn,
                'requirements' => $course->requirements,
                'price' => (float) $course->price,
                'image' => $course->image,
                'video_url' => $course->video_url,
                'duration_weeks' => $course->duration_weeks,
                'difficulty_level' => $course->difficulty_level,
                'is_published' => $course->is_published,
                'category' => $course->category,
                'instructor' => $course->instructor,
                'sections' => $course->sections,
                'reviews_count' => $course->reviews()->count(),
                'students_count' => $course->enrollments()->where('status', 'active')->count(),
                'average_rating' => $course->reviews()->avg('rating') ?? 0,
            ],
            'isEnrolled' => $isEnrolled,
            'enrollment' => $enrollment
        ]);
    }
}