<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403, 'Not authenticated');
        }

        if (!auth()->admin()->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin only.');
        }

        return $next($request);
    }
}