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

        /*
        |--------------------------------------------------------------------------
        | 🔴 ADMIN LOGIN (NO ROLE)
        |--------------------------------------------------------------------------
        */
        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {

            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | 🔵 USER LOGIN (WITH ROLE)
        |--------------------------------------------------------------------------
        */
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            // 🎓 ROLE BASED REDIRECT (ONLY USERS)
            if ($user->role === 'student') {
                return redirect()->route('user.dashboard');
            }

            if ($user->role === 'instructor') {
                return redirect()->route('instructor.dashboard');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ❌ FAILED LOGIN
        |--------------------------------------------------------------------------
        */
        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
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