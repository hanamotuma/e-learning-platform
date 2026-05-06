<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Your cart is empty');
        }
        
        $total = collect($cart)->sum('price');
        
        return Inertia::render('Checkout', [
            'cart' => array_values($cart),
            'total' => $total
        ]);
    }
    
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:telebirr,cbe_birr,credit_card',
        ]);
        
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Your cart is empty');
        }
        
        session()->put('pending_payment', [
            'cart' => $cart,
            'total' => collect($cart)->sum('price'),
            'method' => $request->payment_method
        ]);
        
        return redirect()->route('payment.page');
    }
}