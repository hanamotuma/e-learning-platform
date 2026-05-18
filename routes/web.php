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
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\StudentManagementController;
use App\Http\Controllers\Admin\InstructorManagementController;
use App\Http\Controllers\Admin\CourseApprovalController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\PaymentManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =========================
// PROFILE ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/{id}', [App\Http\Controllers\ProfileController::class, 'publicProfile'])->name('profile.public');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});


// =========================
// HOME
// =========================
Route::get('/', [HomeController::class, 'index'])->name('home');

// =========================
// COURSE SHOW ROUTE (MUST BE BEFORE OTHER COURSE ROUTES)
// =========================
Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
    Route::get('/profile/{id}', [ProfileController::class, 'publicProfile'])->name('profile.public');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
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
    
    // Lesson Management Routes
Route::post('/admin/courses/{courseId}/sections/{sectionId}/lessons', [CourseApprovalController::class, 'addLesson'])->name('admin.lessons.store');
Route::put('/admin/courses/{courseId}/lessons/{lessonId}', [CourseApprovalController::class, 'updateLesson'])->name('admin.lessons.update');
Route::delete('/admin/courses/{courseId}/lessons/{lessonId}', [CourseApprovalController::class, 'deleteLesson'])->name('admin.lessons.destroy');

// Section Management Routes
Route::post('/admin/courses/{courseId}/sections', [CourseApprovalController::class, 'addSection'])->name('admin.sections.store');
Route::put('/admin/courses/{courseId}/sections/{sectionId}', [CourseApprovalController::class, 'updateSection'])->name('admin.sections.update');
Route::delete('/admin/courses/{courseId}/sections/{sectionId}', [CourseApprovalController::class, 'deleteSection'])->name('admin.sections.destroy');
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

// Instructor Routes
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->group(function () {
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('instructor.dashboard');
    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
    Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('instructor.courses.create');
    Route::post('/courses', [InstructorCourseController::class, 'store'])->name('instructor.courses.store');
    Route::get('/courses/{id}/edit', [InstructorCourseController::class, 'edit'])->name('instructor.courses.edit');
    Route::put('/courses/{id}', [InstructorCourseController::class, 'update'])->name('instructor.courses.update');
    Route::delete('/courses/{id}', [InstructorCourseController::class, 'destroy'])->name('instructor.courses.destroy');
    
    Route::get('/students', [InstructorDashboardController::class, 'students'])->name('instructor.students');
    Route::get('/earnings', [InstructorDashboardController::class, 'earnings'])->name('instructor.earnings');
    Route::get('/reviews', [InstructorDashboardController::class, 'reviews'])->name('instructor.reviews');
    Route::get('/reviews/data', [InstructorDashboardController::class, 'getReviewsData'])->name('instructor.reviews.data');
    Route::get('/analytics', [InstructorDashboardController::class, 'analytics'])->name('instructor.analytics');
    Route::get('/analytics/data', [InstructorDashboardController::class, 'getAnalyticsData'])->name('instructor.analytics.data');
   // Settings & Profile
    Route::get('/settings', [InstructorDashboardController::class, 'settings'])->name('instructor.settings');
    Route::patch('/profile', [InstructorDashboardController::class, 'updateProfile'])->name('instructor.profile.update');
    Route::put('/password', [InstructorDashboardController::class, 'updatePassword'])->name('instructor.password.update');
    Route::get('/help', [InstructorDashboardController::class, 'help'])->name('instructor.help');
    
    // Profile update routes
    Route::patch('/profile', [InstructorDashboardController::class, 'updateProfile'])->name('instructor.profile.update');
    Route::put('/password', [InstructorDashboardController::class, 'updatePassword'])->name('instructor.password.update');

   // Instructor Quiz Routes
Route::middleware(['auth', 'role:instructor'])->prefix('instructor/courses/{courseId}/quizzes')->group(function () {
    Route::get('/', [InstructorQuizController::class, 'index'])->name('instructor.quizzes.index');
    Route::get('/create', [InstructorQuizController::class, 'create'])->name('instructor.quizzes.create');
    Route::post('/', [InstructorQuizController::class, 'store'])->name('instructor.quizzes.store');
    Route::get('/{quizId}/edit', [InstructorQuizController::class, 'edit'])->name('instructor.quizzes.edit');
    Route::put('/{quizId}', [InstructorQuizController::class, 'update'])->name('instructor.quizzes.update');
    Route::post('/{quizId}/questions', [InstructorQuizController::class, 'addQuestion'])->name('instructor.quizzes.questions.store');
    Route::put('/{quizId}/questions/{questionId}', [InstructorQuizController::class, 'updateQuestion'])->name('instructor.quizzes.questions.update');
    Route::delete('/{quizId}/questions/{questionId}', [InstructorQuizController::class, 'deleteQuestion'])->name('instructor.quizzes.questions.destroy');
    Route::post('/{quizId}/publish', [InstructorQuizController::class, 'publish'])->name('instructor.quizzes.publish');
    Route::delete('/{quizId}', [InstructorQuizController::class, 'destroy'])->name('instructor.quizzes.destroy');
});

});


// =========================
// REVIEW ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/courses/{courseId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// =========================
// STUDENT DASHBOARD ROUTES
// =========================
Route::middleware(['auth'])->group(function () {
    Route::get('/student/dashboard', [App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/dashboard', [App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('dashboard');
    
    // Fixed the broken profile string syntax here:
    Route::get('/profile/{id}', [ProfileController::class, 'publicProfile'])->name('profile.public');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

   // Student Quiz Routes
Route::middleware(['auth'])->prefix('courses/{courseId}/quizzes')->group(function () {
    Route::get('/{quizId}', [StudentQuizController::class, 'show'])->name('student.quizzes.show');
    Route::post('/{quizId}/start', [StudentQuizController::class, 'start'])->name('student.quizzes.start');
    Route::get('/{quizId}/take/{attemptId}', [StudentQuizController::class, 'take'])->name('student.quizzes.take');
    Route::post('/{quizId}/submit/{attemptId}', [StudentQuizController::class, 'submit'])->name('student.quizzes.submit');
    Route::get('/{quizId}/results/{attemptId}', [StudentQuizController::class, 'results'])->name('student.quizzes.results');
});
});
// =========================
// ADMIN ROUTES
// =========================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Student Management
    Route::get('/students', [App\Http\Controllers\Admin\StudentManagementController::class, 'index'])->name('admin.students.index');
    Route::get('/students/{id}', [App\Http\Controllers\Admin\StudentManagementController::class, 'show'])->name('admin.students.show');
    Route::post('/students/{id}/toggle-status', [App\Http\Controllers\Admin\StudentManagementController::class, 'toggleStatus'])->name('admin.students.toggle-status');
    Route::post('/students/{id}/reset-password', [App\Http\Controllers\Admin\StudentManagementController::class, 'resetPassword'])->name('admin.students.reset-password');
    Route::post('/students/{id}/refund', [App\Http\Controllers\Admin\StudentManagementController::class, 'issueRefund'])->name('admin.students.refund');
    Route::post('/students/{id}/verify', [App\Http\Controllers\Admin\StudentManagementController::class, 'verifyAccount'])->name('admin.students.verify');
    
    // Instructor Management
    Route::get('/instructors', [App\Http\Controllers\Admin\InstructorManagementController::class, 'index'])->name('admin.instructors.index');
    Route::get('/instructors/create', [App\Http\Controllers\Admin\InstructorManagementController::class, 'create'])->name('admin.instructors.create');
    Route::post('/instructors', [App\Http\Controllers\Admin\InstructorManagementController::class, 'store'])->name('admin.instructors.store');
    Route::get('/instructors/{id}', [App\Http\Controllers\Admin\InstructorManagementController::class, 'show'])->name('admin.instructors.show');
    Route::post('/instructors/{id}/approve', [App\Http\Controllers\Admin\InstructorManagementController::class, 'approve'])->name('admin.instructors.approve');
    Route::post('/instructors/{id}/reject', [App\Http\Controllers\Admin\InstructorManagementController::class, 'reject'])->name('admin.instructors.reject');
    Route::post('/instructors/{id}/toggle-status', [App\Http\Controllers\Admin\InstructorManagementController::class, 'toggleStatus'])->name('admin.instructors.toggle-status');
    Route::get('/instructors/{id}/earnings', [App\Http\Controllers\Admin\InstructorManagementController::class, 'earnings'])->name('admin.instructors.earnings');
    
    // Course Management
    Route::get('/courses', [CourseApprovalController::class, 'index'])->name('admin.courses.index');
    Route::get('/courses/create', [CourseApprovalController::class, 'create'])->name('admin.courses.create');
    Route::post('/courses', [CourseApprovalController::class, 'store'])->name('admin.courses.store');
    Route::get('/courses/{id}', [CourseApprovalController::class, 'show'])->name('admin.courses.show');
    Route::get('/courses/{id}/edit', [CourseApprovalController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/courses/{id}', [CourseApprovalController::class, 'update'])->name('admin.courses.update');
    Route::post('/courses/{id}/approve', [CourseApprovalController::class, 'approve'])->name('admin.courses.approve');
    Route::post('/courses/{id}/reject', [CourseApprovalController::class, 'reject'])->name('admin.courses.reject');
    Route::post('/courses/{id}/feature', [CourseApprovalController::class, 'feature'])->name('admin.courses.feature');
    Route::delete('/courses/{id}', [CourseApprovalController::class, 'destroy'])->name('admin.courses.destroy');

    // Course Approval
    Route::get('/courses', [App\Http\Controllers\Admin\CourseApprovalController::class, 'index'])->name('admin.courses.index');
    Route::get('/courses/{id}', [App\Http\Controllers\Admin\CourseApprovalController::class, 'show'])->name('admin.courses.show');
    Route::get('/courses/{id}/edit', [App\Http\Controllers\Admin\CourseApprovalController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/courses/{id}', [App\Http\Controllers\Admin\CourseApprovalController::class, 'update'])->name('admin.courses.update');
    Route::post('/courses/{id}/approve', [App\Http\Controllers\Admin\CourseApprovalController::class, 'approve'])->name('admin.courses.approve');
    Route::post('/courses/{id}/reject', [App\Http\Controllers\Admin\CourseApprovalController::class, 'reject'])->name('admin.courses.reject');
    Route::post('/courses/{id}/feature', [App\Http\Controllers\Admin\CourseApprovalController::class, 'feature'])->name('admin.courses.feature');
    Route::post('/courses/{id}/suspend', [App\Http\Controllers\Admin\CourseApprovalController::class, 'suspend'])->name('admin.courses.suspend');
    Route::delete('/courses/{id}', [App\Http\Controllers\Admin\CourseApprovalController::class, 'destroy'])->name('admin.courses.destroy');
    

     // Categories
    Route::get('/categories', [CategoryManagementController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [CategoryManagementController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{id}', [CategoryManagementController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [CategoryManagementController::class, 'destroy'])->name('admin.categories.destroy');

    // Payment Management
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentManagementController::class, 'index'])->name('admin.payments.index');
    Route::get('/payments/{id}', [App\Http\Controllers\Admin\PaymentManagementController::class, 'show'])->name('admin.payments.show');
    Route::post('/payments/{id}/refund', [App\Http\Controllers\Admin\PaymentManagementController::class, 'refund'])->name('admin.payments.refund');
    Route::get('/payments/export', [App\Http\Controllers\Admin\PaymentManagementController::class, 'export'])->name('admin.payments.export');
    
    // Withdrawal Management
    Route::get('/withdrawals', [App\Http\Controllers\Admin\PaymentManagementController::class, 'withdrawals'])->name('admin.withdrawals.index');
    Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\Admin\PaymentManagementController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{id}/paid', [App\Http\Controllers\Admin\PaymentManagementController::class, 'markWithdrawalPaid'])->name('admin.withdrawals.paid');
    Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\Admin\PaymentManagementController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');

      // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/analytics/data', [App\Http\Controllers\Admin\AnalyticsController::class, 'getData'])->name('admin.analytics.data');

      // Reviews
    Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('/reviews/data', [App\Http\Controllers\Admin\ReviewController::class, 'getData'])->name('admin.reviews.data');
    Route::post('/reviews/{id}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::delete('/reviews/{id}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    
    // Support
    Route::get('/support', [App\Http\Controllers\Admin\SupportController::class, 'index'])->name('admin.support.index');
    Route::get('/support/data', [App\Http\Controllers\Admin\SupportController::class, 'getData'])->name('admin.support.data');
    Route::get('/support/{id}', [App\Http\Controllers\Admin\SupportController::class, 'show'])->name('admin.support.show');
    Route::post('/support/{id}/reply', [App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('admin.support.reply');

   // Coupons
    Route::get('/coupons', [App\Http\Controllers\Admin\CouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('/coupons/data', [App\Http\Controllers\Admin\CouponController::class, 'getData'])->name('admin.coupons.data');
    Route::get('/coupons/create', [App\Http\Controllers\Admin\CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/coupons', [App\Http\Controllers\Admin\CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('/coupons/{id}/edit', [App\Http\Controllers\Admin\CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/coupons/{id}', [App\Http\Controllers\Admin\CouponController::class, 'update'])->name('admin.coupons.update');
    Route::delete('/coupons/{id}', [App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('admin.coupons.destroy');

    // Announcements
    Route::get('/announcements', [App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::get('/announcements/data', [App\Http\Controllers\Admin\AnnouncementController::class, 'getData'])->name('admin.announcements.data');
    Route::get('/announcements/create', [App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('admin.announcements.create');
    Route::post('/announcements', [App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::get('/announcements/{id}/edit', [App\Http\Controllers\Admin\AnnouncementController::class, 'edit'])->name('admin.announcements.edit');
    Route::put('/announcements/{id}', [App\Http\Controllers\Admin\AnnouncementController::class, 'update'])->name('admin.announcements.update');
    Route::post('/announcements/{id}/publish', [App\Http\Controllers\Admin\AnnouncementController::class, 'publish'])->name('admin.announcements.publish');
    Route::delete('/announcements/{id}', [App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

    
    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::get('/settings/data', [App\Http\Controllers\Admin\SettingController::class, 'getData'])->name('admin.settings.data');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
    
     // Help
    Route::get('/help', function () {
        return Inertia::render('Admin/Help/Index');
    })->name('admin.help.index');

       // Quiz Management for Courses
    Route::post('/courses/{courseId}/quizzes', [AdminQuizController::class, 'storeQuiz'])->name('admin.courses.quizzes.store');
    Route::put('/courses/{courseId}/quizzes/{quizId}', [AdminQuizController::class, 'updateQuiz'])->name('admin.courses.quizzes.update');
    Route::delete('/courses/{courseId}/quizzes/{quizId}', [AdminQuizController::class, 'deleteQuiz'])->name('admin.courses.quizzes.destroy');
    Route::post('/courses/{courseId}/quizzes/{quizId}/toggle-publish', [AdminQuizController::class, 'toggleQuizPublish'])->name('admin.courses.quizzes.toggle-publish');
    // Quiz Questions
    Route::get('/quizzes/{quizId}/questions', [AdminQuizController::class, 'questions'])->name('admin.quizzes.questions');
    Route::post('/quizzes/{quizId}/questions', [AdminQuizController::class, 'addQuestion'])->name('admin.quizzes.questions.store');
    Route::put('/quizzes/{quizId}/questions/{questionId}', [AdminQuizController::class, 'updateQuestion'])->name('admin.quizzes.questions.update');
    Route::delete('/quizzes/{quizId}/questions/{questionId}', [AdminQuizController::class, 'deleteQuestion'])->name('admin.quizzes.questions.destroy');
    
    // Quiz Attempts
    Route::get('/quizzes/{quizId}/attempts', [AdminQuizController::class, 'attempts'])->name('admin.quizzes.attempts');
    Route::get('/quizzes/{quizId}/attempts/{attemptId}', [AdminQuizController::class, 'viewAttempt'])->name('admin.quizzes.attempts.show');


    // Admin Profile
    Route::get('/profile/{id}', [App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('admin.profile.show');

    
    // Course routes
    Route::get('/courses', [CourseApprovalController::class, 'index'])->name('admin.courses.index');
    Route::get('/courses/create', [CourseApprovalController::class, 'create'])->name('admin.courses.create');
    Route::post('/courses', [CourseApprovalController::class, 'store'])->name('admin.courses.store');
    Route::get('/courses/{id}', [CourseApprovalController::class, 'show'])->name('admin.courses.show');
    Route::get('/courses/{id}/edit', [CourseApprovalController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/courses/{id}', [CourseApprovalController::class, 'update'])->name('admin.courses.update');
    Route::post('/courses/{id}/approve', [CourseApprovalController::class, 'approve'])->name('admin.courses.approve');
    Route::post('/courses/{id}/reject', [CourseApprovalController::class, 'reject'])->name('admin.courses.reject');
    Route::post('/courses/{id}/feature', [CourseApprovalController::class, 'feature'])->name('admin.courses.feature');
    Route::delete('/courses/{id}', [CourseApprovalController::class, 'destroy'])->name('admin.courses.destroy');

    // Lesson routes
    Route::post('/courses/{courseId}/sections/{sectionId}/lessons', [CourseApprovalController::class, 'addLesson'])->name('admin.lessons.store');
    Route::put('/courses/{courseId}/lessons/{lessonId}', [CourseApprovalController::class, 'updateLesson'])->name('admin.lessons.update');
    Route::delete('/courses/{courseId}/lessons/{lessonId}', [CourseApprovalController::class, 'deleteLesson'])->name('admin.lessons.destroy');
    
    // Section routes
    Route::post('/courses/{courseId}/sections', [CourseApprovalController::class, 'addSection'])->name('admin.sections.store');
    Route::delete('/courses/{courseId}/sections/{sectionId}', [CourseApprovalController::class, 'deleteSection'])->name('admin.sections.destroy');
    
    // Temporary video upload for course creation
Route::post('/admin/temp-upload-video', function (Request $request) {
    $request->validate([
        'video' => 'required|file|mimes:mp4,mov,avi,mkv|max:204800'
    ]);
    
    $path = $request->file('video')->store('temp-videos', 'public');
    
    return response()->json(['path' => '/storage/' . $path]);
})->middleware(['auth', 'role:admin']);
});



// =========================
// AUTH ROUTES
// =========================
require __DIR__.'/auth.php';