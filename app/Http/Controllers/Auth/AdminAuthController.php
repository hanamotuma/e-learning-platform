<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthenticationController extends Controller
{
    /**
     * Show admin login page
     */
    public function create()
    {
        return inertia('Admin/Auth/Login');
    }

    /**
     * Handle admin login
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find admin from admins table
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return back()->withErrors([
                'email' => 'Invalid admin credentials',
            ]);
        }

        // Login admin manually using guard or session
        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Logout admin
     */
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}