<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment; // Ensure this is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Required for Chapa API
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function showPaymentPage()
    {
        $pendingPayment = session()->get('pending_payment');
        
        if (!$pendingPayment) {
            return redirect()->route('home')->with('error', 'No pending payment');
        }
        
        return Inertia::render('Payment/Index', [
            'total' => $pendingPayment['total'],
            'method' => $pendingPayment['method'],
            'cart' => $pendingPayment['cart']
        ]);
    }


    public function initialize(Request $request)
    {
        $tx_ref = uniqid('tx_');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CHAPA_SECRET_KEY'),
        ])->post(env('CHAPA_BASE_URL') . '/transaction/initialize', [
            'amount' => $request->amount,
            'currency' => 'ETB',
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'tx_ref' => $tx_ref,
            'callback_url' => route('payment.callback'),
            'return_url' => url('/payment-success'),
            'customization' => [
                'title' => 'Course Payment',
                'description' => 'Payment for course enrollment',
            ],
        ]);

        $data = $response->json();

        if (isset($data['data']['checkout_url'])) {
            return redirect($data['data']['checkout_url']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }

    public function callback(Request $request)
    {
        $tx_ref = $request->tx_ref;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CHAPA_SECRET_KEY'),
        ])->get(env('CHAPA_BASE_URL') . "/transaction/verify/$tx_ref");

        $data = $response->json();

        if ($data['status'] === 'success') {

            // update enrollment/payment table here

            return redirect('/payment-success');
        }

        return redirect('/payment-failed');
    }

    
    private function completeEnrollmentAndRedirect()
    {
        $pendingPayment = session()->get('pending_payment');
        $paymentCompleted = session()->get('payment_completed');
        
        if (!$pendingPayment || !$paymentCompleted) {
            return redirect()->route('home')->with('error', 'Invalid payment session');
        }
        
        $user = Auth::user();
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
            
            Payment::create([
                'user_id' => $user->id,
                'transaction_id' => $paymentCompleted['transaction_id'],
                'amount' => $pendingPayment['total'],
                'payment_method' => $paymentCompleted['payment_method'],
                'status' => 'completed',
                'payment_data' => json_encode($paymentCompleted)
            ]);
        });
        
        session()->forget(['pending_payment', 'cart', 'payment_completed']);
        
        return redirect()->route('student.dashboard')->with('success', 'Payment successful! You are now enrolled.');
    }
    
    public function paymentCancel()
    {
        session()->forget('pending_payment');
        return Inertia::render('PaymentCancel');
    }

    public function success($tx_ref)
    {
        $payment = Payment::where('transaction_id', $tx_ref)
            ->with('course')
            ->firstOrFail();
        
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