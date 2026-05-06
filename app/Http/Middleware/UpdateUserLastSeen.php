<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; // Ensure this is imported
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if anyone (User or Admin) is logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // 2. Safety Check: Only proceed if the table actually has the column.
            // This allows the middleware to work for BOTH the 'users' and 'admins' tables
            // as long as you have added the column to those tables.
            if (Schema::hasColumn($user->getTable(), 'last_seen_at')) {
                
                // 3. Only update if it's the first time or if 1 minute has passed
                if (!$user->last_seen_at || $user->last_seen_at->diffInMinutes(now()) >= 1) {
                    $user->last_seen_at = now();
                    
                    // Use saveQuietly so we don't trigger "UserUpdated" events 
                    // or change the "updated_at" timestamp unnecessarily
                    $user->saveQuietly();
                }
            }
        }

        return $next($request);
    }
}