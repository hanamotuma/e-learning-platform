<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Enrollment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InstructorCourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['category', 'enrollments'])
            ->where('instructor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $stats = [
            'total_courses' => $courses->total(),
            'total_students' => Enrollment::whereIn('course_id', $courses->pluck('id'))->count(),
            'total_revenue' => Enrollment::whereIn('course_id', $courses->pluck('id'))->sum('amount_paid'),
            'average_rating' => round($courses->avg('rating') ?? 0, 1),
        ];
        
        return Inertia::render('Instructor/Courses/Index', [
            'courses' => $courses,
            'stats' => $stats,
        ]);
    }
    
    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Instructor/Courses/Create', [
            'categories' => $categories,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'image' => 'nullable|image|max:2048',
        ]);
        
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Course::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data = $request->all();
        $data['slug'] = $slug;
        $data['instructor_id'] = Auth::id();
        $data['is_published'] = false;
        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('course-images', 'public');
            $data['image'] = '/storage/' . $path;
        }
        
        $course = Course::create($data);
       
        $course = Course::create([
        'title' => $request->title,
        'slug' => $slug,
        'description' => $request->description,
        'what_you_will_learn' => $request->what_you_will_learn,
        'requirements' => $request->requirements,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'difficulty_level' => $request->difficulty_level,
        'instructor_id' => Auth::id(),
        'image' => $imagePath,
        'status' => 'draft',
        'is_published' => false,
        'approval_status' => 'pending', // Important: pending approval
         ]);
        
        // Notify admin for approval
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'course_pending_approval',
                'title' => '📝 New Course Needs Approval',
                'message' => Auth::user()->name . ' created a new course: "' . $course->title . '"',
                'action_url' => route('admin.courses.edit', $course->id),
            ]);
        }

         // Save sections and lessons
    if ($request->has('sections')) {
        foreach ($request->sections as $sectionData) {
            $section = Section::create([
                'course_id' => $course->id,
                'title' => $sectionData['title'],
                'description' => $sectionData['description'] ?? null,
                'order_position' => $sectionData['order_position'] ?? 0,
            ]);
            
            if (isset($sectionData['lessons'])) {
                foreach ($sectionData['lessons'] as $lessonData) {
                    Lesson::create([
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'] ?? null,
                        'video_url' => $lessonData['video_url'] ?? null,
                        'duration_minutes' => $lessonData['duration_minutes'] ?? 0,
                        'is_free' => $lessonData['is_free'] ?? false,
                    ]);
                }
            }
        }
    }
        // Notify admin about new course
    $admins = User::role('admin')->get();
    foreach ($admins as $admin) {
        Notification::create([
            'user_id' => $admin->id,
            'type' => 'course_pending',
            'title' => '📚 New Course Pending Approval',
            'message' => Auth::user()->full_name . ' submitted "' . $course->title . '" for approval',
            'action_url' => route('admin.courses.show', $course->id),
            'is_read' => false
        ]);
    }

    return redirect()->route('instructor.courses.index')->with('success', 'Course submitted for admin approval. You will be notified once reviewed.');
}
    
    public function edit($id)
    {
        $course = Course::with(['sections' => function($q) {
            $q->orderBy('order_position');
        }, 'sections.lessons' => function($q) {
            $q->orderBy('order');
        }, 'sections.quizzes' => function($q) {
            $q->with('questions.options');
        }])->where('instructor_id', Auth::id())->findOrFail($id);
        
        $categories = Category::all();
        
        return Inertia::render('Instructor/Courses/Edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'what_you_will_learn' => 'nullable|string',
            'requirements' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'image' => 'nullable|image|max:2048',
        ]);
        
        $data = $request->except(['image']);
        
        if ($request->hasFile('image')) {
            if ($course->image) {
                $oldPath = str_replace('/storage/', '', $course->image);
                \Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('course-images', 'public');
            $data['image'] = '/storage/' . $path;
        }
        
        $course->update($data);
        
        return redirect()->back()->with('success', 'Course updated successfully!');
    }
    
    public function addSection(Request $request, $courseId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $maxOrder = Section::where('course_id', $courseId)->max('order_position') ?? 0;
        
        $section = Section::create([
            'course_id' => $courseId,
            'title' => $request->title,
            'description' => $request->description,
            'order_position' => $maxOrder + 1,
        ]);
        
        return response()->json($section);
    }
    
    public function updateSectionOrder(Request $request, $courseId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        foreach ($request->sections as $index => $sectionId) {
            Section::where('id', $sectionId)->update(['order_position' => $index + 1]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function deleteSection($courseId, $sectionId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        $section = Section::where('course_id', $courseId)->findOrFail($sectionId);
        $section->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function addLesson(Request $request, $courseId, $sectionId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer',
            'is_free' => 'boolean',
        ]);
        
        $maxOrder = Lesson::where('section_id', $sectionId)->max('order') ?? 0;
        
        $lesson = Lesson::create([
            'course_id' => $courseId,
            'section_id' => $sectionId,
            'title' => $request->title,
            'content' => $request->content,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes,
            'is_free' => $request->is_free ?? false,
            'order' => $maxOrder + 1,
        ]);
        
        return response()->json($lesson);
    }
    
    public function updateLessonOrder(Request $request, $courseId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        foreach ($request->lessons as $index => $lessonId) {
            Lesson::where('id', $lessonId)->update(['order' => $index + 1]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function deleteLesson($courseId, $lessonId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        $lesson = Lesson::where('course_id', $courseId)->findOrFail($lessonId);
        $lesson->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function addQuiz(Request $request, $courseId, $sectionId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
        ]);
        
        $quiz = Quiz::create([
            'course_id' => $courseId,
            'section_id' => $sectionId,
            'title' => $request->title,
            'description' => $request->description,
            'time_limit_minutes' => $request->time_limit_minutes,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts,
            'is_published' => true,
        ]);
        
        return response()->json($quiz);
    }
    
    public function addQuestion(Request $request, $courseId, $quizId)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($courseId);
        
        $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:mcq,true_false,short_answer',
            'marks' => 'required|integer|min:1',
            'options' => 'required_if:type,mcq|array',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'boolean',
            'correct_text' => 'required_if:type,short_answer|nullable|string',
        ]);
        
        $question = Question::create([
            'quiz_id' => $quizId,
            'question_text' => $request->question_text,
            'type' => $request->type,
            'marks' => $request->marks,
        ]);
        
        if ($request->type === 'mcq') {
            foreach ($request->options as $option) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'] ?? false,
                ]);
            }
        } elseif ($request->type === 'short_answer') {
            \App\Models\AdminAnswer::create([
                'question_id' => $question->id,
                'correct_text' => $request->correct_text,
            ]);
        }
        
        return response()->json($question);
    }
    
    public function publish($id)
    {
        $course = Course::where('instructor_id', Auth::id())->findOrFail($id);
        
        // Check if course has at least one section and lesson
        $hasContent = Section::where('course_id', $id)->exists() &&
                      Lesson::where('course_id', $id)->exists();
        
        if (!$hasContent) {
            return redirect()->back()->with('error', 'Add at least one section and lesson before publishing.');
        }
        
        // Submit for admin approval
        $course->update(['is_published' => false, 'status' => 'pending']);
        
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'course_ready_for_review',
                'title' => '📚 Course Ready for Review',
                'message' => Auth::user()->name . ' submitted "' . $course->title . '" for approval.',
                'action_url' => route('admin.courses.edit', $course->id),
            ]);
        }
        
        return redirect()->back()->with('success', 'Course submitted for admin approval. You will be notified once approved.');
    }
}