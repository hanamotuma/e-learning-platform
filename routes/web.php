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
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Instructor\InstructorCourseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =========================
// HOME
// =========================
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// =========================
// COURSE SHOW ROUTE (MUST BE BEFORE OTHER COURSE ROUTES)
// =========================
Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.show');

// =========================
// AUTH DASHBOARDS
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
    
    Route::get('/instructor/dashboard', function () {
        return Inertia::render('Instructor/Dashboard');
    })->middleware('role:instructor')->name('instructor.dashboard');
    
    Route::get('/student/dashboard', function () {
        return Inertia::render('Student/Dashboard');
    })->middleware('role:student')->name('student.dashboard');
    
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
// CHECKOUT & PAYMENT ROUTES
// =========================
Route::prefix('checkout')->middleware(['auth'])->group(function () {
    Route::get('/{slug}', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/{slug}/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/callback/{tx_ref}', [CheckoutController::class, 'callback'])->name('checkout.callback');
    Route::get('/success/{tx_ref}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/failed/{tx_ref}', [CheckoutController::class, 'failed'])->name('checkout.failed');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('student.payments');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/payments', [PaymentController::class, 'adminIndex'])->name('admin.payments');
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
Route::middleware(['auth'])->group(function () {
    Route::get('/certificate/{id}', [CertificateController::class, 'show'])->name('certificate.show');
    Route::get('/certificate/{id}/download', [CertificateController::class, 'download'])->name('certificate.download');
    Route::get('/verify/{number}', [CertificateController::class, 'verify'])->name('certificate.verify');
});

// =========================
// INSTRUCTOR COURSE MANAGEMENT
// =========================
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
    Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('instructor.courses.create');
    Route::post('/courses', [InstructorCourseController::class, 'store'])->name('instructor.courses.store');
    Route::get('/courses/{id}/edit', [InstructorCourseController::class, 'edit'])->name('instructor.courses.edit');
    Route::put('/courses/{id}', [InstructorCourseController::class, 'update'])->name('instructor.courses.update');
    Route::post('/courses/{id}/publish', [InstructorCourseController::class, 'publish'])->name('instructor.courses.publish');
    
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
// ADMIN COURSE MANAGEMENT
// =========================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard-data', [AdminDashboardController::class, 'index'])->name('admin.dashboard.data');
    Route::get('/courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
    Route::post('/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
    Route::put('/courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('admin.courses.destroy');
    Route::get('/courses/{course}/students-data', [AdminCourseController::class, 'students'])->name('admin.courses.students');
    Route::post('/courses/{course}/approve', [AdminCourseController::class, 'approve'])->name('admin.courses.approve');
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
// SUPPORT TICKET ROUTES
// =========================
Route::middleware(['auth'])->prefix('support')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/create', function () { return Inertia::render('Support/Create'); })->name('support.create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/{id}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/{id}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/support')->group(function () {
    Route::get('/', [SupportTicketController::class, 'adminIndex'])->name('admin.support.index');
    Route::get('/{id}', [SupportTicketController::class, 'adminShow'])->name('admin.support.show');
    Route::post('/{id}/status', [SupportTicketController::class, 'updateStatus'])->name('admin.support.status');
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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/reports', [ReportController::class, 'adminReports'])->name('admin.reports');
});

// =========================
// REVIEW ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/courses/{courseId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/reviews', [ReviewController::class, 'adminIndex'])->name('admin.reviews.index');
    Route::post('/reviews/{id}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
});

// =========================
// AUTH ROUTES
// =========================
require __DIR__.'/auth.php';