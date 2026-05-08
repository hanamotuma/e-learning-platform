<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function showPaymentPage()
    {
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return redirect()->route('home')->with('error', 'No pending payment');
        }
        
        return Inertia::render('PaymentPage', [
            'total' => $pendingPayment['total'],
            'method' => $pendingPayment['method'],
            'cart' => $pendingPayment['cart']
        ]);
    }
    
    public function processTelebirr(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^09[0-9]{8}$/'
        ]);
        
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return response()->json(['success' => false, 'message' => 'No pending payment']);
        }
        
        // Generate fake transaction ID for Telebirr
        $transactionId = 'TB_' . strtoupper(Str::random(16)) . '_' . time();
        
        // Store payment info in session
        session()->put('payment_completed', [
            'transaction_id' => $transactionId,
            'payment_method' => 'telebirr',
            'amount' => $pendingPayment['total'],
            'phone_number' => $request->phone_number,
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        // Complete enrollment
        return $this->completeEnrollmentAndRedirect();
    }
    
    public function processCBE(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|regex:/^10[0-9]{10}$/',
            'phone_number' => 'required|string|regex:/^09[0-9]{8}$/'
        ]);
        
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return response()->json(['success' => false, 'message' => 'No pending payment']);
        }
        
        // Generate fake transaction ID for CBE Birr
        $transactionId = 'CBE_' . strtoupper(Str::random(16)) . '_' . time();
        
        // Store payment info in session
        session()->put('payment_completed', [
            'transaction_id' => $transactionId,
            'payment_method' => 'cbe_birr',
            'amount' => $pendingPayment['total'],
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        // Complete enrollment
        return $this->completeEnrollmentAndRedirect();
    }
    
    public function initializeChapa(Request $request)
    {
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return response()->json(['success' => false, 'message' => 'No pending payment']);
        }
        
        // Generate fake transaction ID for Chapa
        $transactionId = 'CHAPA_' . strtoupper(Str::random(16)) . '_' . time();
        
        // Simulate Chapa payment initialization
        // In a real scenario, this would call Chapa API
        $checkoutUrl = route('payment.chapa.callback', ['tx_ref' => $transactionId]);
        
        return response()->json([
            'success' => true,
            'transaction_id' => $transactionId,
            'checkout_url' => $checkoutUrl
        ]);
    }
    
    public function chapaCallback(Request $request)
    {
        $transactionId = $request->get('tx_ref');
        $status = $request->get('status', 'success');
        
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return redirect()->route('home')->with('error', 'No pending payment');
        }
        
        if ($status === 'success') {
            // Store payment info in session
            session()->put('payment_completed', [
                'transaction_id' => $transactionId,
                'payment_method' => 'chapa',
                'amount' => $pendingPayment['total'],
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // Complete enrollment
            return $this->completeEnrollmentAndRedirect();
        }
        
        return redirect()->route('payment.cancel')->with('error', 'Payment failed or cancelled');
    }
    
    private function completeEnrollmentAndRedirect()
    {
        $pendingPayment = session()->get('pending_payment');
        $paymentCompleted = session()->get('payment_completed');
        
        if (!$pendingPayment || !$paymentCompleted) {
            return redirect()->route('home')->with('error', 'Invalid payment session');
        }
        
        $user = auth::user();
        $courseIds = collect($pendingPayment['cart'])->pluck('id')->toArray();
        
        DB::transaction(function () use ($user, $courseIds, $paymentCompleted, $pendingPayment) {
            foreach ($courseIds as $courseId) {
                $course = Course::find($courseId);
                
                if ($course && !$user->courses()->where('course_id', $courseId)->exists()) {
                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $courseId,
                        'progress' => 0,
                        'completed' => false,
                        'enrolled_at' => now(),
                        'payment_status' => 'completed',
                        'amount_paid' => $course->price,
                        'transaction_id' => $paymentCompleted['transaction_id'],
                        'payment_method' => $paymentCompleted['payment_method']
                    ]);
                    
                    $course->increment('students');
                }
            }
            
            // Create payment record
            \App\Models\Payment::create([
                'user_id' => $user->id,
                'transaction_id' => $paymentCompleted['transaction_id'],
                'amount' => $pendingPayment['total'],
                'payment_method' => $paymentCompleted['payment_method'],
                'status' => 'completed',
                'payment_data' => json_encode($paymentCompleted)
            ]);
        });
        
        // Clear sessions
        session()->forget('pending_payment');
        session()->forget('cart');
        session()->forget('payment_completed');
        
        // Redirect to student dashboard with success message
        return redirect()->route('student.dashboard')->with('success', 'Payment successful! You are now enrolled in your courses.');
    }
    
    public function paymentSuccess()
    {
        // This method is kept for backward compatibility
        return redirect()->route('student.dashboard');
    }
    
    public function paymentCancel()
    {
        session()->forget('pending_payment');
        return Inertia::render('PaymentCancel');
    }

    public function success($tx_ref)
{
    $payment = Payment::where('chapa_tx_ref', $tx_ref)
        ->with('course')
        ->firstOrFail();
    
    // Create enrollment after successful payment
    $enrollment = Enrollment::firstOrCreate([
        'user_id' => $payment->user_id,
        'course_id' => $payment->course_id,
    ], [
        'progress_percentage' => 0,
        'status' => 'active',
        'enrolled_at' => now(),
        'last_accessed_at' => now(),
        'amount_paid' => $payment->amount,
    ]);
    
    return Inertia::render('Payment/Success', [
        'payment' => $payment,
        'course' => $payment->course,
    ]);
}
}