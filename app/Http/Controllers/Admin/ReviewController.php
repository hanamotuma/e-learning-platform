<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * List all reviews for a specific course
     */
    public function index(Course $course)
    {
        return Inertia::render('Admin/Courses/Reviews', [
            'course' => $course,
            'reviews' => Review::where('course_id', $course->id)
                ->with('user:id,name,email')
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Toggle the approval status of a review
     */
    public function toggleApproval(Review $review)
    {
        $review->update([
            'is_approved' => !$review->is_approved
        ]);

        return back()->with('success', 'Review status updated.');
    }

    /**
     * Save or update the instructor's response
     */
    public function respond(Request $request, Review $review)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        // Storing as an array as per your model $casts
        $responses = $review->instructor_response ?? [];
        $responses[] = [
            'body' => $validated['message'],
            'created_at' => now()->toDateTimeString(),
            'admin_id' => auth()->id()
        ];

        $review->update([
            'instructor_response' => $responses
        ]);

        return back()->with('success', 'Response added.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}