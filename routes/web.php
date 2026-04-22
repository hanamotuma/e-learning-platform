<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =========================
// HOME
// =========================
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');


// =========================
// AUTH DASHBOARDS (ROLE BASED)
// =========================
Route::middleware(['auth'])->group(function () {

    // ADMIN DASHBOARD
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->middleware('role:admin')->name('admin.dashboard');

    // INSTRUCTOR DASHBOARD
    Route::get('/instructor/dashboard', function () {
        return Inertia::render('Instructor/Dashboard');
    })->middleware('role:instructor')->name('instructor.dashboard');

    // USER / STUDENT DASHBOARD
    Route::middleware('auth')->group(function () {
 
    Route::get('/student/dashboard', function () {
        return Inertia::render('Student/Dashboard');
    })->middleware('role:student')->name('student.dashboard');
});

    // PROFILE PAGE
    Route::get('/profile', function () {
        return Inertia::render('Profile/Edit');
    })->name('profile.edit');
});


// =========================
// COURSE ROUTES
// =========================
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/{course}', [CourseController::class, 'show'])->name('courses.show');

    Route::get('/{course}/learn', [CourseController::class, 'learn'])
        ->middleware('auth')
        ->name('courses.learn');
});


// =========================
// AUTH ROUTES
// =========================
require __DIR__.'/auth.php';