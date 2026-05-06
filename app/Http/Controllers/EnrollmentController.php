<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    // 1. Show the Checkout Page
    public function checkout(Course $course)
    {
        return Inertia::render('Payments/Checkout', [
            'course' => $course
        ]);
        
    }

    // 2. Start Chapa Transaction
    public function initiatePayment(Request $request, Course $course)
    {
        $user = Auth::user();
        $tx_ref = 'TXN-' . strtoupper(uniqid());

        $response = Http::withToken(env('CHAPA_SECRET_KEY'))
            ->post('https://api.chapa.co/v1/transaction/initialize', [
                'amount' => $course->price,
                'currency' => 'ETB',
                'email' => $user->email,
                'tx_ref' => $tx_ref,
                'callback_url' => route('enrollment.callback', ['tx_ref' => $tx_ref, 'course_id' => $course->id]),
                'return_url' => route('payments.success'),
            ]);

        if ($response->successful()) {
            return Inertia::location($response->json()['data']['checkout_url']);
        }

        return back()->with('error', 'Payment initialization failed.');
    }

    // 3. The Callback (Where the Enrollment is actually created)
    public function callback(Request $request, $tx_ref)
    {
        $response = Http::withToken(env('CHAPA_SECRET_KEY'))
            ->get("https://api.chapa.co/v1/transaction/verify/{$tx_ref}");

        if ($response->json()['status'] === 'success') {
            // Use your Enrollment model to save the data
            $enrollment = Enrollment::create([
                'user_id' => Auth::id(),
                'course_id' => $request->course_id,
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            // Use the relationship you defined in the Model to save the payment
            $enrollment->payment()->create([
                'user_id' => Auth::id(),
                'amount' => $response->json()['data']['amount'],
                'currency' => 'ETB',
                'status' => 'successful',
                'transaction_reference' => $tx_ref,
                'paid_at' => now(),
            ]);

            return redirect()->route('payments.success');
        }

        return redirect()->route('courses.index')->with('error', 'Payment verification failed.');
    }
}