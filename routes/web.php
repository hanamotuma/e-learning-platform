
<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

// Auth routes
require __DIR__.'/auth.php';