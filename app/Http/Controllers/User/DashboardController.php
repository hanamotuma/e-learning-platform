<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
{
    return Inertia::render('Student/Dashboard', [
        // ... existing data (enrolled courses, etc.)
        
        'ticketStats' => [
            'total' => auth()->user()->tickets()->count(),
            'open' => auth()->user()->tickets()->where('status', 'open')->count(),
            'recent' => auth()->user()->tickets()
                            ->latest()
                            ->take(3) // Only show the 3 most recent tickets
                            ->get()
        ]
    ]);
}
}
