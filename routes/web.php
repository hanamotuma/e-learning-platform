<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

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
// NOTIFICATION ROUTES
// =========================

Route::middleware(['auth'])->group(function () {
    // For Inertia page rendering
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    // For JSON API requests
    Route::get('/notifications/json', [NotificationController::class, 'getNotificationsJson'])->name('notifications.json');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


// =========================
// AUTH ROUTES
// =========================
require __DIR__.'/auth.php';