<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Render the checkout page with the items from the cart.
     */
    public function checkout($id)
    {
        // Fetch a single course using the route parameter
        $course = Course::findOrFail($id);
        
        return Inertia::render('Payments/Checkout', [
            'course' => $course,
            'totalAmount' => (float) $course->price,
            'isFree' => (float) $course->price <= 0
        ]);
    }

    /**
     * Process the payment or direct enrollment if free.
     */
    public function process(Request $request)
    {
        // Debugging line to inspect incoming request data. Remove in production!
        // Allow the payment method to be passed as 'free' or a regular string for paid courses
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $course = Course::findOrFail($request->course_id);
        $totalAmount = (float) $course->price;

        /* ---------------------------------------------------------
           1. HANDLE FREE COURSES (0 ETB)
        --------------------------------------------------------- */
       
        if ($totalAmount <= 0) {
            return DB::transaction(function () use ($user, $course) {
                Enrollment::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                if ($user->role === 'user') {
                    $user->update(['role' => 'student']);
                }

                return redirect()->route('student.dashboard')
                    ->with('message', 'Enrollment successful! You can now access your course.');
            });
        }

        /* ---------------------------------------------------------
           2. HANDLE PAID COURSES (VIA CHAPA)
        --------------------------------------------------------- */
        $tx_ref = 'CART-' . strtoupper(uniqid());

        
        $payment = Payment::create([
    'user_id'                => $user->id,
    'course_id'              => $course->id,
    'amount'                 => $totalAmount,
    'currency'               => 'ETB',
    'transaction_reference'  => $tx_ref,
    'chapa_tx_ref'           => $tx_ref, // Both must be filled to avoid the SQL error
    'status'                 => 'pending',
    'payment_method'         => $request->payment_method,
    
    // Add these to satisfy the 500 error if they are NOT NULL in your DB:
    'enrollment_id'          => null, 
    'payment_details'        => null,
    'chapa_response'         => null,
]);



        // app\Http\Controllers\PaymentController.php

$response = Http::withToken('CHASECK_TEST-dksfxAHkpQrS8QJj6HTazMz7lamXAmbf')
    ->post('https://api.chapa.co/v1/transaction/initialize', [
        'amount' => $totalAmount,
        'currency' => 'ETB',
        'email' => $user->email,
        'first_name' => $user->name,
        'tx_ref' => $tx_ref,
        'callback_url' => route('payments.callback', ['tx_ref' => $tx_ref]),
        'return_url' => route('payments.callback', ['tx_ref' => $tx_ref]), 
        'customization' => [
        'title' => "Payment for ",
        'description' => "Order Ref " . $tx_ref
        ]
    ]);
//dd($response->json());
// Remove the dd($response->successful()) once you verify this works
if ($response->successful()) {
    $data = $response->json();
    if ($data['status'] === 'success') {
        return Inertia::location($data['data']['checkout_url']);
    }
}

return back()->with('error', 'Payment failed to initialize.');

        return back()->with('error', 'Unable to initialize payment with Chapa. Please try again.');
    }

    /**
     * Webhook/Callback from Chapa to verify payment.
     */
    public function callback($tx_ref)
    {
        // Debugging line to check if the secret key is loaded correctly. Remove in production!
       // \Log::info('CHAPA_SECRET_KEY: ' . env('CHAPA_SECRET_KEY'));
        $response = Http::withToken(env('CHAPA_SECRET_KEY'))
            ->get("https://api.chapa.co/v1/transaction/verify/{$tx_ref}");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'success') {
                
                DB::transaction(function () use ($tx_ref) {
                    $payment = Payment::where('chapa_tx_ref', $tx_ref)->first();
                    
                    if ($payment && $payment->status !== 'completed') {
                        $payment->update(['status' => 'completed']);

                        $courseIds = json_decode($payment->metadata);

                        foreach ($courseIds as $courseId) {
                            Enrollment::firstOrCreate([
                                'user_id' => $payment->user_id,
                                'course_id' => $courseId,
                            ]);
                        }

                        $user = $payment->user;
                        if ($user->role === 'user') {
                            $user->update(['role' => 'student']);
                        }
                    }
                });

                return redirect()->route('student.dashboard')->with('message', 'Payment successful! Courses unlocked.');
            }
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
    }

    /**
     * Success page view.
     */
    public function success()
    {
        return Inertia::render('Payments/Success', [
            'message' => 'Your enrollment is confirmed!'
        ]);
    }
}