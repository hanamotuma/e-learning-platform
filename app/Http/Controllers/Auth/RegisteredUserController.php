<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

  public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Generate username from name
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->name));
    $username = $baseUsername;
    $counter = 1;
    
    while (User::where('username', $username)->exists()) {
        $username = $baseUsername . $counter;
        $counter++;
    }

    $user = User::create([
        'full_name' => $request->name,
        'username' => $username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_active' => true,
    ]);
    
    $user->assignRole('student');   
    event(new Registered($user));

    Auth::login($user);

    // Check session for redirect after checkout
    $redirectAfterCheckout = session('redirect_after_checkout');
    $redirectAfterLogin = session('redirect_after_login');
    
    // Clear the session variables
    session()->forget('redirect_after_checkout');
    session()->forget('redirect_after_login');
    
    // Redirect to checkout if that was the intent
    if ($redirectAfterCheckout || $redirectAfterLogin === '/checkout') {
        return redirect('/checkout');
    }
    
    // Check if there's cart data
    if (session()->has('checkout_cart') || session()->get('redirect_after_checkout')) {
        return redirect('/checkout');
    }

    return redirect(route('student.dashboard'));
}
}