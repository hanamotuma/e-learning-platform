<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * LOGIN PAGE (ONE PAGE)
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * LOGIN (ADMIN + USER TABLE SYSTEM)
     */
    public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // 🔴 STEP 1: Attempt to find an Admin
    $admin = Admin::where('email', $credentials['email'])->first();

    if ($admin && Hash::check($credentials['password'], $admin->password)) {
        // Manually log into the 'admin' guard
        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    // 🔵 STEP 2: If no admin found, attempt to find a User
    $user = User::where('email', $credentials['email'])->first();

    if ($user && Hash::check($credentials['password'], $user->password)) {
        // Manually log into the default 'web' guard
        Auth::guard('web')->login($user, $request->boolean('remember'));
        
        $request->session()->regenerate();

        // Redirect based on the 'role' column inside the User table
        return match ($user->role) {
            'instructor' => redirect()->route('instructor.dashboard'),
            default      => redirect()->route('dashboard'), // Students
        };
    }

    // ❌ STEP 3: If neither match, return error
    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}