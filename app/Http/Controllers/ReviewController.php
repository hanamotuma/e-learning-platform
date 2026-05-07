<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use App\Models\Enrollment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);
        
        $reviews = Review::with('user')
            ->where('course_id', $courseId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $averageRating = $course->reviews()->where('is_approved', true)->avg('rating') ?? 0;
        $totalReviews = $course->reviews()->where('is_approved', true)->count();
        
        $userReview = null;
        if (Auth::check()) {
            $userReview = Review::where('user_id', Auth::id())
                ->where('course_id', $courseId)
                ->first();
        }
        
        return Inertia::render('Courses/Reviews', [
            'course' => $course,
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $totalReviews,
            'userReview' => $userReview,
        ]);
    }
    
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:10|max:1000',
        ]);
        
        $course = Course::findOrFail($courseId);
        
        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'You must be enrolled in this course to leave a review.');
        }
        
        // Check if user already reviewed
        $existingReview = Review::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
        
        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this course.');
        }
        
        $review = Review::create([
            'user_id' => Auth::id(),
            'course_id' => $courseId,
            'rating' => $request->rating,
            'review' => $request->review,
            'is_approved' => false, // Requires admin approval
        ]);
        
        // Notify instructor
        Notification::create([
            'user_id' => $course->instructor_id,
            'type' => 'new_review',
            'title' => '⭐ New Course Review',
            'message' => Auth::user()->name . ' left a ' . $request->rating . '-star review on "' . $course->title . '"',
            'action_url' => route('admin.reviews.show', $review->id),
        ]);
        
        // Notify admin
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'review_needs_approval',
                'title' => '📝 New Review Needs Approval',
                'message' => Auth::user()->name . ' reviewed "' . $course->title . '"',
                'action_url' => route('admin.reviews.index'),
            ]);
        }
        
        return redirect()->back()->with('success', 'Thank you for your review! It will appear after admin approval.');
    }
    
    public function adminIndex()
    {
        $reviews = Review::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }
    
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);
        
        // Update course average rating
        $course = $review->course;
        $avgRating = $course->reviews()->where('is_approved', true)->avg('rating');
        $course->update(['rating' => round($avgRating, 1)]);
        
        // Notify user
        Notification::create([
            'user_id' => $review->user_id,
            'type' => 'review_approved',
            'title' => '✅ Your Review is Live!',
            'message' => 'Your review for "' . $course->title . '" has been approved and is now visible.',
            'action_url' => route('courses.show', $course->slug),
        ]);
        
        return redirect()->back()->with('success', 'Review approved.');
    }
    
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        
        return redirect()->back()->with('success', 'Review deleted.');
    }
}