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

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
    
    $user->assignRole('student');   
    event(new Registered($user));

    Auth::login($user);

    // Check if there's a redirect after checkout
    $redirectAfterCheckout = session('redirect_after_checkout') ?? $request->session()->get('redirect_after_checkout');
    $redirectAfterLogin = session('redirect_after_login') ?? $request->session()->get('redirect_after_login');
    
    if ($redirectAfterCheckout || $redirectAfterLogin === '/checkout') {
        $request->session()->forget('redirect_after_checkout');
        $request->session()->forget('redirect_after_login');
        return redirect('/checkout');
    }

    return redirect(route('student.dashboard', absolute: false));
}
}