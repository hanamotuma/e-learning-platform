<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function checkout(Course $course)
    {
        // Check if user is already enrolled
        $existingEnrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->course_id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('courses.show', $course->course_id)
                ->with('info', 'You are already enrolled in this course.');
        }

        return Inertia::render('Payments/Checkout', [
            'course' => $course,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,course_id',
            'payment_method' => 'required|string',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user->user_id)
            ->where('course_id', $course->course_id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('courses.show', $course->course_id)
                ->with('info', 'You are already enrolled in this course.');
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->user_id,
            'course_id' => $course->course_id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->user_id,
            'enrollment_id' => $enrollment->enrollment_id,
            'amount' => $course->price ?? 0,
            'currency' => 'ETB',
            'status' => 'successful', // For demo purposes
            'payment_method' => $request->payment_method,
            'transaction_reference' => 'TXN-' . uniqid(),
            'paid_at' => now(),
        ]);

        return redirect()->route('payments.success');
    }

    public function success()
    {
        return Inertia::render('Payments/Success');
    }

    public function cancel()
    {
        return Inertia::render('Payments/Cancel');
    }
}