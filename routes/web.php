<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ProgressTrackingController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\AttemptReviewController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\QuizAttemptController ;

/*
|--------------------------------------------------------------------------
| PUBLIC & USER AUTH
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Home'))->name('home');

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
    Route::get('/profile', fn () => Inertia::render('Profile/Edit'))
        ->name('profile.edit');

    Route::get('/dashboard', fn () => Inertia::render('User/Dashboard'))
        ->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| ADMIN SYSTEM (Prefix: admin / Name: admin.)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // GUEST ADMIN
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
        Route::get('/register', [AdminAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.submit');
    });

    // AUTH ADMIN
    Route::middleware('auth:admin')->group(function () {
        
        Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        // LMS RESOURCES
        Route::resource('categories', CategoryController::class);
        Route::resource('courses', CourseController::class);

        // SECTIONS
        Route::post('/courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        // LESSONS
        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('/sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

        // RESOURCES (Files)
        Route::post('/lessons/{lesson}/resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');

        // PROGRESS TRACKING
        Route::post('/lessons/{lesson}/progress', [ProgressTrackingController::class, 'update'])->name('progress.update');
        // Fixed method name to match your controller: courseProgress
        Route::get('/courses/{course}/progress', [ProgressTrackingController::class, 'courseProgress'])->name('progress.course');
        
        // ADMIN CERTIFICATE VIEW (If admin wants to see user certs)
        Route::get('/certificates/{courseId}', [CertificateController::class, 'show'])->name('certificates.show');

        Route::post('/sections/{section}/quizzes', [QuizController::class, 'store'])
        ->name('sections.quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('admin.quizzes.questions.store');
    
    // Delete a single question
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('admin.questions.destroy');
    Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])
        ->name('admin.questions.store'); // This results in 'admin.questions.store'
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('admin.quizzes.questions.store');
Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('admin.questions.destroy');
Route::get('/admin/attempts/{id}', [AttemptReviewController::class, 'show'])->name('admin.attempts.show');    });
});
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('courses/{course}/reviews', [ReviewController::class, 'index'])->name('courses.reviews');
    Route::patch('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');
    Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    // List all attempts
    Route::get('/attempts', [QuizAttemptController::class, 'index'])->name('attempts.index');
    
    // View specific attempt details
    Route::get('/attempts/{id}', [QuizAttemptController::class, 'show'])->name('attempts.show');
    
    // Manual grading route
    Route::patch('/answers/{answer}/score', [QuizAttemptController::class, 'updateScore'])->name('answers.update-score');
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


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Laravel Breeze / UI)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
