<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Notifications\CourseUpdatedNotification;


class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('instructor_id', Auth::id())
            ->with('category')
            ->latest()
            ->paginate(12);
        
        return Inertia::render('Instructor/Courses/Index', [
            'courses' => $courses
        ]);
    }
    
    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Instructor/Courses/Create', [
            'categories' => $categories
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:courses',
            'slug' => 'nullable|string|unique:courses',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'required|in:draft,published,archived',
            'level' => 'required|string',
            'what_you_ll_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'target_audience' => 'nullable|string',
        ]);
        $validated['instructor_id'] = Auth::id();
        Course::create($validated);
        $course = Course::create($validated);
        
        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('courses', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }
        
        // Set instructor_id to current user
        $validated['instructor_id'] = Auth::id();
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        
        Auth::user()->notify(new CourseUpdatedNotification($course));
        
        return redirect()->route('instructor.dashboard')
            ->with('success', 'Course created successfully!');
    }
    
    public function edit($id)
    {
        $course = Course::where('instructor_id', Auth::id())
            ->findOrFail($id);
        
        $categories = Category::all();
        
        return Inertia::render('Instructor/Courses/Edit', [
            'course' => $course,
            'categories' => $categories
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $course = Course::where('instructor_id', Auth::id())
            ->findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:courses,title,' . $course->id,
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'required|in:draft,published,archived',
            'level' => 'required|string',
            'what_you_ll_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'target_audience' => 'nullable|string',
        ]);
        
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                $oldPath = str_replace('/storage/', '', $course->thumbnail);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('thumbnail')->store('courses', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }
        
        $course->update($validated);
        
        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course updated successfully!');
    }
    

}