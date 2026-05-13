<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index($slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        
        $existingEnrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->exists();
        
        if ($existingEnrollment) {
            return redirect()->route('course.learn', $course->slug);
        }
        
        return Inertia::render('Checkout/Index', [
            'course' => $course,
            'user' => Auth::user(),
        ]);
    }
    
    public function process(Request $request, $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $user = Auth::user();
        
        $request->validate([
            'payment_method' => 'required|in:telebirr,cbe,card',
            'phone' => 'required_if:payment_method,telebirr,cbe',
        ]);
        
        $tx_ref = 'TX-' . Str::random(12) . '-' . time();
        
        // Store payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'transaction_reference' => $tx_ref,
            'chapa_tx_ref' => $tx_ref,
            'amount' => $course->price,
            'currency' => 'ETB',
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_details' => ['phone' => $request->phone],
        ]);
        
        // Initialize Chapa payment
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('CHAPA_SECRET_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.chapa.co/v1/transaction/initialize', [
                'amount' => $course->price,
                'currency' => 'ETB',
                'email' => $user->email,
                'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'tx_ref' => $tx_ref,
                'callback_url' => route('checkout.callback', $tx_ref),
                'return_url' => route('checkout.success', $tx_ref),
                'customization' => [
                    'title' => 'LearnHub - ' . $course->title,
                    'description' => 'Course Enrollment Payment',
                ],
            ]);
            
            $data = $response->json();
            
            if ($data && $data['status'] == 'success') {
                $payment->update([
                    'chapa_response' => $data,
                ]);
                
                return redirect($data['data']['checkout_url']);
            }
            
            return redirect()->back()->with('error', 'Payment initialization failed. Please try again.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Payment service error: ' . $e->getMessage());
        }
    }

    public function show(Course $course)
    {
        return Inertia::render('Checkout/Index', [
            'course' => $course,
          
        ]);
    }
    
    public function callback($tx_ref)
    {
        $payment = Payment::where('chapa_tx_ref', $tx_ref)->firstOrFail();
        
        // Verify payment with Chapa
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('CHAPA_SECRET_KEY'),
            ])->get('https://api.chapa.co/v1/transaction/verify/' . $tx_ref);
            
            $data = $response->json();
            
            if ($data && $data['status'] == 'success') {
                $payment->update([
                    'status' => 'successful',
                    'paid_at' => now(),
                    'chapa_response' => $data,
                ]);
                
                // Create enrollment
                $enrollment = Enrollment::create([
                    'user_id' => $payment->user_id,
                    'course_id' => $payment->course_id,
                    'progress_percentage' => 0,
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'last_accessed_at' => now(),
                    'amount_paid' => $payment->amount,
                ]);
                
                // Send notifications
                $course = Course::find($payment->course_id);
                $user = \App\Models\User::find($payment->user_id);
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'payment_success',
                    'title' => '✅ Payment Successful!',
                    'message' => 'Your payment for "' . $course->title . '" was successful. Start learning now!',
                    'action_url' => route('course.learn', $course->slug),
                ]);
                
                Notification::create([
                    'user_id' => $course->instructor_id,
                    'type' => 'new_enrollment_paid',
                    'title' => '💰 New Paid Enrollment',
                    'message' => $user->name . ' purchased your course "' . $course->title . '" for ' . number_format($payment->amount) . ' ETB',
                    'action_url' => route('instructor.courses.show', $course->id),
                ]);
                
                return redirect()->route('checkout.success', $tx_ref);
            }
            
            $payment->update(['status' => 'failed']);
            return redirect()->route('checkout.failed', $tx_ref);
            
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return redirect()->route('checkout.failed', $tx_ref);
        }
    }
    
    public function success($tx_ref)
    {
        $payment = Payment::where('chapa_tx_ref', $tx_ref)
            ->with('course')
            ->firstOrFail();
        
        return Inertia::render('Checkout/Success', [
            'payment' => $payment,
            'course' => $payment->course,
        ]);
    }
    
    public function failed($tx_ref)
    {
        $payment = Payment::where('chapa_tx_ref', $tx_ref)->first();
        
        return Inertia::render('Checkout/Failed', [
            'payment' => $payment,
        ]);
    }
}