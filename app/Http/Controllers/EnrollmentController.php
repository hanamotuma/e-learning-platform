<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    // Show enrollment form for a course
    public function show($courseId)
    {
        $course = Course::with('category')->findOrFail($courseId);
        
        // Check if user is already enrolled
        $existingEnrollment = Enrollment::where('user_id', auth::id())
            ->where('course_id', $courseId)
            ->first();
        
        if ($existingEnrollment && $existingEnrollment->status === 'active') {
            return redirect()->route('student.courses.show', $courseId)
                ->with('message', 'You are already enrolled in this course!');
        }
        
        return Inertia::render('Student/Checkout', [
            'course' => $course,
            'existing_enrollment' => $existingEnrollment
        ]);
    }
    
    // Process enrollment and payment
    public function enroll(Request $request, $courseId)
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,manual',
        ]);
        
        $course = Course::findOrFail($courseId);
        $user = auth::user();
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'pending'])
            ->first();
        
        if ($existingEnrollment) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }
        
        DB::beginTransaction();
        
        try {
            // Create enrollment record
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'status' => $request->payment_method === 'manual' ? 'pending' : 'active',
                'amount_paid' => $course->price,
                'enrolled_at' => $request->payment_method === 'manual' ? null : now(),
                'progress_percentage' => 0
            ]);
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'enrollment_id' => $enrollment->id,
                'transaction_id' => 'TXN_' . now()->timestamp. '_' . $user->id,
                'amount' => $course->price,
                'status' => $request->payment_method === 'manual' ? 'pending' : 'completed',
                'payment_method' => $request->payment_method,
                'paid_at' => $request->payment_method === 'manual' ? null : now(),
            ]);
            
            DB::commit();
            
            // Trigger notification
            $this->sendEnrollmentNotification($user, $course, $enrollment);
            
            if ($request->payment_method === 'manual') {
                return redirect()->route('student.dashboard')
                    ->with('success', 'Your enrollment request has been submitted. Please complete the payment to access the course.');
            }
            
            return redirect()->route('student.courses.show', $courseId)
                ->with('success', 'Successfully enrolled in ' . $course->title . '!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process enrollment. Please try again.');
        }
    }
    
    // Manual payment confirmation (for admin)
    public function confirmPayment($enrollmentId)
    {
        $enrollment = Enrollment::with(['course', 'user'])->findOrFail($enrollmentId);
        
        // Check if admin
        if (auth::user()->role !== 'admin') {
            abort(403);
        }
        
        DB::beginTransaction();
        
        try {
            // Update enrollment
            $enrollment->update([
                'status' => 'active',
                'enrolled_at' => now()
            ]);
            
            // Update payment
            $enrollment->payment->update([
                'status' => 'completed',
                'paid_at' => now()
            ]);
            
            DB::commit();
            
            // Send notification to student
            // You can implement notification here
            
            return redirect()->back()->with('success', 'Payment confirmed. Student can now access the course.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to confirm payment.');
        }
    }
    
    // View user's enrolled courses
    public function myCourses()
    {
        $enrollments = Enrollment::with('course')
            ->where('user_id', auth::id())
            ->where('status', 'active')
            ->latest()
            ->paginate(12);
        
        return Inertia::render('Student/MyCourses', [
            'enrollments' => $enrollments
        ]);
    }
    
    // Track course progress
    public function trackProgress($courseId)
    {
        $enrollment = Enrollment::where('user_id', auth::id())
            ->where('course_id', $courseId)
            ->firstOrFail();
        
        return Inertia::render('Student/CourseProgress', [
            'enrollment' => $enrollment,
            'course' => $enrollment->course
        ]);
    }
    
    // Update progress (called when user completes lessons)
    public function updateProgress(Request $request, $courseId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100'
        ]);
        
        $enrollment = Enrollment::where('user_id', auth::id())
            ->where('course_id', $courseId)
            ->firstOrFail();
        
        $enrollment->update([
            'progress_percentage' => $request->progress_percentage,
            'completed_at' => $request->progress_percentage >= 100 ? now() : $enrollment->completed_at
        ]);
        
        // If course completed, generate certificate
        if ($request->progress_percentage >= 100 && !$enrollment->completed_at) {
            // Trigger certificate generation
            // You can implement this later
        }
        
        return response()->json(['success' => true]);
    }
    
    // Admin: View all enrollments
    public function adminIndex(Request $request)
    {
        $enrollments = Enrollment::with(['user', 'course'])
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('course', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);
        
        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'filters' => $request->only(['status', 'search'])
        ]);
    }
    
    private function sendEnrollmentNotification($user, $course, $enrollment)
    {
        // Create notification record
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title' => 'Enrollment Successful!',
            'message' => "You have successfully enrolled in {$course->title}. Start learning now!",
            'type' => 'enrollment',
            'is_read' => false
        ]);
    }
}