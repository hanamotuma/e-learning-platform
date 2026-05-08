<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareSessionData
{
    public function handle(Request $request, Closure $next): Response
    {
        // Share session data from sessionStorage via request if needed
        if ($request->has('redirect_to_checkout')) {
            session(['redirect_after_checkout' => true]);
        }
        
        return $next($request);
    }
}