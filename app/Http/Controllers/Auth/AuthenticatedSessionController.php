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
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * LOGIN PAGE
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
   public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    // Check for redirect after checkout
    $redirectAfterCheckout = session('redirect_after_checkout') ?? $request->session()->get('redirect_after_checkout');
    $redirectAfterLogin = session('redirect_after_login') ?? $request->session()->get('redirect_after_login');
    
    if ($redirectAfterCheckout || $redirectAfterLogin === '/checkout') {
        $request->session()->forget('redirect_after_checkout');
        $request->session()->forget('redirect_after_login');
        return redirect('/checkout');
    }

    $user = Auth::user();
    
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('instructor')) {
        return redirect()->route('instructor.dashboard');
    }

    return redirect()->intended('/student/dashboard');
}

    /**
     * LOGOUT
     */
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}