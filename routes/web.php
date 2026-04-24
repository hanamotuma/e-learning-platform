<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ProgressTrackingController;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Home'))->name('home');


/*
|--------------------------------------------------------------------------
| USER AUTH (Laravel default auth system)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/profile', fn () => Inertia::render('Profile/Edit'))
        ->name('profile.edit');

    Route::get('/dashboard', fn () => Inertia::render('User/Dashboard'))
        ->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| ADMIN AUTH SYSTEM (SEPARATE GUARD)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------
    | GUEST ADMIN ROUTES
    |--------------------------
    */
    Route::middleware('guest:admin')->group(function () {

        Route::get('/login', [AdminAuthController::class, 'create'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'store'])
            ->name('login.submit');

        Route::get('/register', [AdminAuthController::class, 'registerForm'])
            ->name('register');

        Route::post('/register', [AdminAuthController::class, 'register'])
            ->name('register.submit');
    });

    /*
    |--------------------------
    | AUTH ADMIN ROUTES
    |--------------------------
    */
    Route::middleware('auth:admin')->group(function () {

        // 🧭 ADMIN DASHBOARD
        Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))
            ->name('dashboard');

        Route::post('/logout', [AdminAuthController::class, 'destroy'])
            ->name('logout');

        /*
        |--------------------------------------------------------------------------
        | LMS ADMIN SYSTEM
        |--------------------------------------------------------------------------
        */

        // CATEGORIES
        Route::resource('categories', CategoryController::class);

        // COURSES
        Route::resource('courses', CourseController::class);

        // SECTIONS (Course → Section)
        Route::post('/courses/{course}/sections', [SectionController::class, 'store'])
            ->name('sections.store');

        Route::put('/sections/{section}', [SectionController::class, 'update'])
            ->name('sections.update');

        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])
            ->name('sections.destroy');

        // LESSONS (Section → Lesson)
        Route::post('/sections/{section}/lessons', [LessonController::class, 'store'])
            ->name('lessons.store');

        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])
            ->name('lessons.destroy');
     Route::get('/lessons/{lesson}', [LessonController::class, 'show'])
        ->name('lessons.show');

        Route::post('/lessons/{lesson}/resources', [ResourceController::class, 'store'])
            ->name('resources.store');

         Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])
         ->name('resources.destroy');
             Route::post('/lessons/{lesson}/progress', 
        [ProgressTrackingController::class, 'update']
    )->name('progress.update');

    Route::get('/courses/{course}/progress', 
        [ProgressTrackingController::class, 'course.progress']
    )->name('progress.course');

    });
});


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Laravel Breeze / UI)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';