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

    $user = Auth::user();
    
    // Check for redirect after checkout
    $redirectAfterCheckout = session('redirect_after_checkout');
    if ($redirectAfterCheckout) {
        session()->forget('redirect_after_checkout');
        return redirect('/checkout');
    }
    
    // Redirect based on role
   if ($request->user()->hasRole('admin')) {
        return redirect()->intended(route('admin.dashboard'));
    }
    if ($request->user()->hasRole('instructor')) {
        return redirect()->intended(route('instructor.dashboard'));
    }

    return redirect()->intended(route('student.dashboard'));
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