<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show($id)
    {
        $admin = Admin::findOrFail($id);
        
        return Inertia::render('Admin/Profile/Index', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'created_at' => $admin->created_at,
                'last_login_at' => $admin->last_login_at
            ]
        ]);
    }
}