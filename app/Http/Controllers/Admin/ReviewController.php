<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Course;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reviews/Index');
    }

    public function getData()
    {
        $reviews = Review::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'is_approved' => $review->is_approved,
                    'created_at' => $review->created_at,
                    'user' => $review->user ? [
                        'id' => $review->user->id,
                        'name' => $review->user->full_name ?? $review->user->name,
                        'email' => $review->user->email
                    ] : null,
                    'course' => $review->course ? [
                        'id' => $review->course->id,
                        'title' => $review->course->title
                    ] : null
                ];
            });

        $stats = [
            'total' => Review::count(),
            'approved' => Review::where('is_approved', true)->count(),
            'pending' => Review::where('is_approved', false)->count(),
            'average_rating' => round(Review::avg('rating') ?? 0, 1)
        ];

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats
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
            'title' => '✅ Review Approved',
            'message' => 'Your review has been approved and is now visible on the course page.',
            'action_url' => route('courses.show', $course->slug),
            'is_read' => false
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['success' => true]);
    }

    public function flag(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        Notification::create([
            'user_id' => 1, // Admin ID
            'type' => 'review_flagged',
            'title' => '⚠️ Review Flagged',
            'message' => "Review #{$review->id} has been flagged. Reason: " . $request->reason,
            'action_url' => route('admin.reviews.index'),
            'is_read' => false
        ]);

        return response()->json(['success' => true]);
    }

    public function respond(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        Notification::create([
            'user_id' => $review->user_id,
            'type' => 'review_response',
            'title' => '📝 Instructor Responded',
            'message' => 'An instructor has responded to your review: ' . $request->response,
            'action_url' => route('courses.show', $review->course->slug),
            'is_read' => false
        ]);

        return response()->json(['success' => true]);
    }
}