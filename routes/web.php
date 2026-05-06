<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\CategoryController as InstructorCategoryController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ProgressTrackingController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\AttemptReviewController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\QuizAttemptController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Student\StudentDashboardController;

/*
|--------------------------------------------------------------------------
| PUBLIC & USER AUTH
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Home'))->name('home');

// Auth routes - FIXED: These should point to actual Vue components
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

// Auth routes (for Laravel Breeze/Jetstream - keep this)
require __DIR__.'/auth.php';

// API routes for payment (public access for webhooks)
Route::post('/payment/chapa/callback', [PaymentController::class, 'chapaCallback'])->name('payment.callback');

// Public course routes
Route::get('/api/courses', [App\Http\Controllers\CourseController::class, 'index'])->name('api.courses.index');
Route::get('/api/courses/{course}', [App\Http\Controllers\CourseController::class, 'show'])->name('api.courses.show');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('instructor')) return redirect()->route('instructor.dashboard');
        return redirect()->route('student.dashboard');
    })->name('dashboard');
    
    // Cart Routes (using session)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{course}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{course}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    
    // My Courses
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
    
    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'processPayment'])->name('checkout.process');
    
    // Payment routes
    Route::get('/payment/page', [PaymentController::class, 'showPaymentPage'])->name('payment.page');
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    
    // Payment Processing Routes
Route::post('/api/payment/telebirr', [PaymentController::class, 'processTelebirr'])->name('payment.telebirr');
Route::post('/api/payment/cbe', [PaymentController::class, 'processCBE'])->name('payment.cbe');
Route::post('/api/payment/chapa/initialize', [PaymentController::class, 'initializeChapa'])->name('payment.chapa.initialize');
Route::get('/payment/chapa/callback', [PaymentController::class, 'chapaCallback'])->name('payment.chapa.callback');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/json', [NotificationController::class, 'getNotificationsJson'])->name('notifications.json');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    
    // Enrollment routes
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course}/progress', [EnrollmentController::class, 'trackProgress'])->name('course.progress');
    Route::post('/courses/{course}/progress', [EnrollmentController::class, 'updateProgress'])->name('course.progress.update');
    
    // Helper pages
    Route::get('/help', fn () => Inertia::render('Help'))->name('help');
    Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');
    
    /*
    |--------------------------------------------------------------------------
    | STUDENT ROUTES (Role: student)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::get('/student/course/{course}/learn', [App\Http\Controllers\CourseController::class, 'learn'])->name('student.course.learn');
    });
    
    /*
    |--------------------------------------------------------------------------
    | INSTRUCTOR ROUTES (Role: instructor)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');
        
        // Course Management
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');
        
        // Category Management
        Route::post('/categories', [InstructorCategoryController::class, 'store'])->name('categories.store');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (Role: admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        
        Route::resource('categories', CategoryController::class);
        Route::resource('courses', AdminCourseController::class);
        Route::post('/courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
        
        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('/sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        
        Route::post('/lessons/{lesson}/resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
        
        Route::post('/lessons/{lesson}/progress', [ProgressTrackingController::class, 'update'])->name('progress.update');
        Route::get('/courses/{course}/progress', [ProgressTrackingController::class, 'courseProgress'])->name('progress.course');
        Route::get('/certificates/{courseId}', [CertificateController::class, 'show'])->name('certificates.show');
        
        Route::post('/sections/{section}/quizzes', [QuizController::class, 'store'])->name('sections.quizzes.store');
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('admin.quizzes.questions.store');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('admin.questions.destroy');
        
        Route::get('/attempts', [QuizAttemptController::class, 'index'])->name('attempts.index');
        Route::get('/attempts/{id}', [QuizAttemptController::class, 'show'])->name('attempts.show');
        Route::patch('/answers/{answer}/score', [QuizAttemptController::class, 'updateScore'])->name('answers.update-score');
        
        Route::get('courses/{course}/reviews', [ReviewController::class, 'index'])->name('courses.reviews');
        Route::patch('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');
        Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        
        Route::get('/enrollments', [EnrollmentController::class, 'adminIndex'])->name('enrollments.index');
        Route::post('/enrollments/{enrollment}/confirm-payment', [EnrollmentController::class, 'confirmPayment'])->name('enrollments.confirm-payment');
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC API ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/api/categories', function () {
    return response()->json(['categories' => App\Models\Category::all()]);
});

/*
|--------------------------------------------------------------------------
| ADMIN AUTH ROUTES (No auth required for login)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
        Route::get('/register', [AdminAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.submit');
    });
});