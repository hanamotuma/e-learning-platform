<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
{
    // 1. Check if logged in via admin guard
    if (!auth()->guard('admin')->check()) {
        abort(403, 'Not authenticated as Admin');
    }

    $admin = auth()->guard('admin')->user();

    // 2. Flexible check: allow ANY valid admin role to see reports
    // Add all the roles you want to allow in this array
    $allowedRoles = ['super_admin', 'manager', 'admin']; 

    if (!in_array($admin->role, $allowedRoles)) {
        abort(403, 'Unauthorized. Your role ('.$admin->role.') is not allowed here.');
    }

    return $next($request);
}
}