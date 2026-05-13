<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Instructor\InstructorDashboardController;
use App\Http\Controllers\Instructor\InstructorCourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =========================
// HOME
// =========================
Route::get('/', [HomeController::class, 'index'])->name('home');

// =========================
// COURSE SHOW ROUTE (MUST BE BEFORE OTHER COURSE ROUTES)
// =========================
Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.show');


Route::middleware(['auth'])->group(function () {
    
    
    Route::get('/student/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('student.dashboard');
    
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    });



// =========================
// COURSE ROUTES
// =========================
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/{slug}', [CourseController::class, 'show'])->name('courses.show');
    
    Route::middleware(['auth'])->group(function () {
        Route::get('/{slug}/learn', [CourseController::class, 'learn'])->name('course.learn');
        Route::post('/{courseId}/enroll', [EnrollmentController::class, 'enroll'])->name('course.enroll');
        Route::post('/{courseId}/lessons/{lessonId}/complete', [LessonProgressController::class, 'markComplete'])->name('lesson.complete');
    });
});

// =========================
// ENROLLMENT ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::post('/enrollment/{enrollmentId}/progress', [EnrollmentController::class, 'updateProgress'])->name('enrollment.progress');
});

// =========================
// API ROUTES FOR ENROLLMENTS
// =========================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/enrollments', function () {
        $enrollments = \App\Models\Enrollment::with(['course.instructor'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $enrollments,
            'total' => $enrollments->count()
        ]);
    });
});

// =========================
// CHECKOUT & PAYMENT ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{courseId}', function ($courseId) {
        return Inertia::render('Checkout/Index');
    })->name('checkout.index');
    
    Route::get('/checkout/{course}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::get('/checkout/{slug}', [CheckoutController::class, 'index'])->name('checkout.single');
    Route::post('/checkout/{slug}/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/callback/{tx_ref}', [CheckoutController::class, 'callback'])->name('checkout.callback');
    Route::get('/checkout/success/{tx_ref}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/failed/{tx_ref}', [CheckoutController::class, 'failed'])->name('checkout.failed');
    // Checkout route
Route::get('/checkout/{courseId}', function ($courseId) {
    return Inertia::render('Checkout/Index');
})->name('checkout.single');
    
    Route::get('/payments', [PaymentController::class, 'index'])->name('student.payments');
    Route::get('/payment/success', function () {
        return Inertia::render('Payment/Success');
    })->name('payment.success');
    Route::post('/pay', [PaymentController::class, 'initialize']);
Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');
});



// =========================
// QUIZ ROUTES
// =========================
Route::middleware(['auth'])->prefix('quizzes')->group(function () {
    Route::get('/{id}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/{id}/start', [QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/take/{attemptId}', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/submit/{attemptId}', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::post('/save-progress/{attemptId}', [QuizController::class, 'saveProgress'])->name('quizzes.save-progress');
    Route::get('/results/{attemptId}', [QuizController::class, 'results'])->name('quizzes.results');
});

// =========================
// CERTIFICATE ROUTES
// =========================
Route::middleware(['auth'])->prefix('certificate')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/generate/{courseId}', [CertificateController::class, 'generate'])->name('certificate.generate');
    Route::get('/download/{id}', [CertificateController::class, 'download'])->name('certificate.download');
});

// =========================
// INSTRUCTOR DASHBOARD & COURSE MANAGEMENT - FIXED
// =========================
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->group(function () {
    // Dashboard
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('instructor.dashboard');
    
    // Course Management
    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
    Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('instructor.courses.create');
    Route::post('/courses', [InstructorCourseController::class, 'store'])->name('instructor.courses.store');
    Route::get('/courses/{id}/edit', [InstructorCourseController::class, 'edit'])->name('instructor.courses.edit');
    Route::put('/courses/{id}', [InstructorCourseController::class, 'update'])->name('instructor.courses.update');
    Route::delete('/courses/{id}', [InstructorCourseController::class, 'destroy'])->name('instructor.courses.destroy');
    Route::post('/courses/{id}/publish', [InstructorCourseController::class, 'publish'])->name('instructor.courses.publish');
    
    // Students
    Route::get('/students', [InstructorDashboardController::class, 'students'])->name('instructor.students');
    
    // Earnings
    Route::get('/earnings', [InstructorDashboardController::class, 'earnings'])->name('instructor.earnings');
    
    // Sections
    Route::post('/courses/{courseId}/sections', [InstructorCourseController::class, 'addSection'])->name('instructor.sections.store');
    Route::put('/courses/{courseId}/sections/order', [InstructorCourseController::class, 'updateSectionOrder'])->name('instructor.sections.order');
    Route::delete('/courses/{courseId}/sections/{sectionId}', [InstructorCourseController::class, 'deleteSection'])->name('instructor.sections.destroy');
    
    // Lessons
    Route::post('/courses/{courseId}/sections/{sectionId}/lessons', [InstructorCourseController::class, 'addLesson'])->name('instructor.lessons.store');
    Route::put('/courses/{courseId}/lessons/order', [InstructorCourseController::class, 'updateLessonOrder'])->name('instructor.lessons.order');
    Route::delete('/courses/{courseId}/lessons/{lessonId}', [InstructorCourseController::class, 'deleteLesson'])->name('instructor.lessons.destroy');
    
    // Quizzes
    Route::post('/courses/{courseId}/sections/{sectionId}/quizzes', [InstructorCourseController::class, 'addQuiz'])->name('instructor.quizzes.store');
    Route::post('/courses/{courseId}/quizzes/{quizId}/questions', [InstructorCourseController::class, 'addQuestion'])->name('instructor.questions.store');
});



// =========================
// NOTIFICATION ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/json', [NotificationController::class, 'getNotificationsJson'])->name('notifications.json');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// =========================
// PROFILE ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/{id}', [App\Http\Controllers\ProfileController::class, 'publicProfile'])->name('profile.public');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
// =========================
// SUPPORT TICKET ROUTES
// =========================
Route::middleware(['auth'])->prefix('support')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/create', function () { return Inertia::render('Support/Create'); })->name('support.create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/{id}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/{id}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
});


// =========================
// CHATBOT ROUTES
// =========================
Route::middleware(['auth'])->prefix('chatbot')->group(function () {
    Route::get('/', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/send', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
    Route::post('/end/{id}', [ChatbotController::class, 'endSession'])->name('chatbot.end');
});

// =========================
// REPORT ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/instructor/reports', [ReportController::class, 'instructorReports'])
        ->middleware('role:instructor')
        ->name('instructor.reports');
});


// =========================
// REVIEW ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/courses/{courseId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});



// =========================
// AUTH ROUTES
// =========================
require __DIR__.'/auth.php';