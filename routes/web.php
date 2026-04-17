
<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;

// Home page
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Course routes
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/{course}/learn', [CourseController::class, 'learn'])->name('courses.learn')->middleware('auth');
});
// Dashboard route (required for all auth tests)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Settings routes (for profile and password management)
Route::middleware('auth')->group(function () {
    // Profile settings
    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Password settings
    Route::get('/settings/password', [ProfileController::class, 'passwordEdit'])->name('password.edit');
    Route::put('/settings/password', [ProfileController::class, 'passwordUpdate'])->name('password.update');
});
// Auth routes

require __DIR__.'/auth.php';