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
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate username if not provided
        $username = $request->username;
        if (!$username) {
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->name));
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
        }

        // Create user
        $user = User::create([
            'full_name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        if ($user->email === 'hanimtnm@gmail.com') {
        $user->assignRole('admin');
        event(new Registered($user));
        Auth::login($user);
        return redirect(route('admin.dashboard'));
    }
        
        // Assign student role
        $user->assignRole('student');
        
        event(new Registered($user));
        
        Auth::login($user);
        
        // Check for redirect after checkout
        $redirectAfterCheckout = session('redirect_after_checkout');
        if ($redirectAfterCheckout) {
            session()->forget('redirect_after_checkout');
            return redirect('/checkout');
        }
        
        return redirect()->intended(route('student.dashboard'));
    }
}