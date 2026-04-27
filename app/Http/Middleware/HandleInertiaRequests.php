<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
   public function share(Request $request): array
{
    // 1. Get the authenticated user (from any guard)
    $user = $request->user();

    return array_merge(parent::share($request), [
        'name' => config('app.name'),
        
        'auth' => [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name ?? $user->full_name, // Handle different column names
                // This checks if the model is an Admin model
                'role' => ($user instanceof \App\Models\Admin) ? 'admin' : 'student',
            ] : null,
        ],

        'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
    ]);
}
}
