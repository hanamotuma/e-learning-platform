<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseApprovalController extends Controller
{
    public function index(Request $request)
{
    $query = Course::with(['instructor', 'category']);
    
    if ($request->status === 'pending') {
        $query->where('status', 'pending');
    } elseif ($request->status === 'published') {
        $query->where('is_published', true);
    } elseif ($request->status === 'draft') {
        $query->where('is_published', false)->where('status', 'draft');
    }
    
    if ($request->search) {
        $query->where('title', 'like', "%{$request->search}%");
    }
    
    $courses = $query->orderBy('created_at', 'desc')->paginate(20);
    
    $stats = [
        'total' => Course::count(),
        'published' => Course::where('is_published', true)->count(),
        'pending' => Course::where('status', 'pending')->count(),
        'draft' => Course::where('is_published', false)->where('status', 'draft')->count(),
        'featured' => Course::where('is_featured', true)->count(),
    ];
    
    return Inertia::render('Admin/Courses/Index', [
        'courses' => $courses,
        'stats' => $stats,
        'filters' => $request->only(['status', 'search']),
        'categories' => Category::all(),
    ]);
}
    
    public function create()
    {
        $categories = Category::all();
        $instructors = User::role('instructor')->where('is_active', true)->get();
        
        return Inertia::render('Admin/Courses/Create', [
            'categories' => $categories,
            'instructors' => $instructors,
        ]);
    }
    
    // FIXED STORE METHOD - This saves the course
   public function store(Request $request)
{
    \Log::info('Course creation started', $request->all());
    
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'difficulty_level' => 'required|in:beginner,intermediate,advanced',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'what_you_will_learn' => 'nullable|string',
        'requirements' => 'nullable|string',
        // REMOVED: instructor_id validation to allow for dynamic assignment
    ]);
    
    // Generate unique slug
    $slug = Str::slug($request->title);
    $originalSlug = $slug;
    $counter = 1;
    while (Course::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
   // Handle image upload with Intervention
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'courses/' . $filename;
            
            // Resize and save image
            $img = Image::make($image);
            $img->resize(800, 450, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::disk('public')->put($path, (string) $img->encode());
            $imagePath = '/storage/' . $path;
        }
    
    // Create course - SET is_published = true for immediate display
    $course = Course::create([
        'title' => $request->title,
        'slug' => $slug,
        'description' => $request->description,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'difficulty_level' => $request->difficulty_level,
        'instructor_id' => \Auth::id(), 
        'what_you_will_learn' => $request->what_you_will_learn,
        'requirements' => $request->requirements,
        'image' => $imagePath,
        'status' => 'approved',
        'is_published' => true,
        'students_count' => 0,
        'reviews_count' => 0,
        'rating' => 0,
        'duration_hours' => 40,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Save sections and lessons if provided
    if ($request->has('sections')) {
        foreach ($request->sections as $sectionData) {
            $section = Section::create([
                'course_id' => $course->id,
                'title' => $sectionData['title'],
                'description' => $sectionData['description'] ?? null,
                'order_position' => $sectionData['order_position'] ?? 0,
            ]);
            
            if (isset($sectionData['lessons']) && is_array($sectionData['lessons'])) {
                foreach ($sectionData['lessons'] as $lessonData) {
                    Lesson::create([
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'] ?? null,
                        'video_url' => $lessonData['video_url'] ?? null,
                        'duration_minutes' => $lessonData['duration_minutes'] ?? 0,
                        'is_free' => $lessonData['is_free'] ?? false,
                        'order' => $lessonData['order'] ?? 0,
                    ]);
                }
            }
        }
    }
    
    \Log::info('Course created', ['course_id' => $course->id]);
    
    return redirect()->route('admin.courses.index')->with('success', 'Course created and published successfully!');
}
    
    public function show($id)
{
    $course = Course::with([
        'instructor',
        'category',
        'sections' => function($q) {
            $q->orderBy('order_position');
        },
        'sections.lessons' => function($q) {
            $q->orderBy('order');
        },
        'sections.quizzes' => function($q) {
            $q->withCount('questions');
        }
    ])->findOrFail($id);
    
    return Inertia::render('Admin/Courses/Show', [
        'course' => $course,
    ]);
}
    
    public function edit($id)
    {
        $course = Course::with(['sections.lessons', 'category'])->findOrFail($id);
        $categories = Category::all();
        $instructors = User::role('instructor')->get();
        
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'categories' => $categories,
            'instructors' => $instructors,
        ]);
    }
    
    public function update(Request $request, $id)
{
    $course = Course::findOrFail($id);
    
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'difficulty_level' => 'required|in:beginner,intermediate,advanced',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);
    
    $data = $request->except(['image']);
    
    // Handle image upload
    if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($course->image) {
            $oldPath = str_replace('/storage/', '', $course->image);
            Storage::disk('public')->delete($oldPath);
        }
        
         $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'courses/' . $filename;
            $img = Image::make($image);
            $img->resize(800, 450, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::disk('public')->put($path, (string) $img->encode());
            $data['image'] = '/storage/' . $path;
        
    }
    
    $course->update($data);
    
    return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully');
}
    
    public function approve($id)
    {
        $course = Course::findOrFail($id);
        $course->update([
            'approval_status' => 'approved',
            'status' => 'approved',
            'is_published' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);
        
        // Notify instructor
        Notification::create([
            'user_id' => $course->instructor_id,
            'type' => 'course_approved',
            'title' => 'Course Approved',
            'message' => "Congratulations! Your course '{$course->title}' has been approved and is now live.",
            'action_url' => route('instructor.courses.edit', $course->id),
            'is_read' => false,
        ]);
        
        return redirect()->back()->with('success', 'Course approved and published');
    }
    
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
        ]);
        
        $course = Course::findOrFail($id);
        $course->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'is_published' => false,
            'status' => 'rejected',
        ]);

        
    // Notify instructor with feedback
    Notification::create([
        'user_id' => $course->instructor_id,
        'type' => 'course_rejected',
        'title' => 'Course Needs Revision',
        'message' => 'Your course "' . $course->title . '" was rejected. Reason: ' . $request->reason,
        'action_url' => route('instructor.courses.edit', $course->id),
        'is_read' => false
    ]);
        
        return redirect()->back()->with('error', 'Course rejected');
    }
    
    public function feature($id)
    {
        $course = Course::findOrFail($id);
        $course->update(['is_featured' => !$course->is_featured]);
        
        return response()->json(['success' => true]);
    }
    
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        
        if ($course->image) {
            $oldPath = str_replace('/storage/', '', $course->image);
            Storage::disk('public')->delete($oldPath);
        }
        
        $course->delete();
        
        return redirect()->back()->with('success', 'Course deleted');
    }
    
    // LESSON MANAGEMENT
    public function addLesson(Request $request, $courseId, $sectionId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:204800',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer',
            'is_free' => 'boolean',
        ]);
        
        $videoUrl = $request->video_url;
        
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('course-videos', 'public');
            $videoUrl = '/storage/' . $path;
        }
        
        $lesson = Lesson::create([
            'course_id' => $courseId,
            'section_id' => $sectionId,
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $videoUrl,
            'duration_minutes' => $request->duration_minutes ?? 0,
            'is_free' => $request->is_free ?? false,
            'order' => Lesson::where('section_id', $sectionId)->max('order') + 1,
        ]);
        
        return response()->json(['success' => true, 'lesson' => $lesson]);
    }
    
    public function updateLesson(Request $request, $courseId, $lessonId)
    {
        $lesson = Lesson::where('course_id', $courseId)->findOrFail($lessonId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer',
            'is_free' => 'boolean',
        ]);
        
        $lesson->update([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes ?? 0,
            'is_free' => $request->is_free ?? false,
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function deleteLesson($courseId, $lessonId)
    {
        $lesson = Lesson::where('course_id', $courseId)->findOrFail($lessonId);
        
        if ($lesson->video_url && strpos($lesson->video_url, '/storage/') === 0) {
            $path = str_replace('/storage/', '', $lesson->video_url);
            Storage::disk('public')->delete($path);
        }
        
        $lesson->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function addSection(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $section = Section::create([
            'course_id' => $courseId,
            'title' => $request->title,
            'description' => $request->description,
            'order_position' => Section::where('course_id', $courseId)->max('order_position') + 1,
        ]);
        
        return response()->json(['success' => true, 'section' => $section]);
    }
    
    public function deleteSection($courseId, $sectionId)
    {
        $section = Section::where('course_id', $courseId)->findOrFail($sectionId);
        
        // Delete all lessons in this section
        foreach ($section->lessons as $lesson) {
            if ($lesson->video_url && strpos($lesson->video_url, '/storage/') === 0) {
                $path = str_replace('/storage/', '', $lesson->video_url);
                Storage::disk('public')->delete($path);
            }
        }
        
        $section->delete();
        
        return response()->json(['success' => true]);
    }
}