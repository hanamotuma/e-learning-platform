<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\{ProfileController, HomeController, PaymentController};
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\EnrollmentController;

use App\Http\Controllers\Admin\ChatbotController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\{
    DashboardController, UserController, ReportController, 
    NotificationController, CategoryController, CourseController, 
    SectionController, LessonController, ResourceController, 
    ProgressTrackingController, CertificateController, QuizController, 
    QuestionController, ReviewController, QuizAttemptController,
    AdminSupportTicketController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/test-key', function() {
    return "Your key is: " . env('GEMINI_API_KEY');
});
/*
|--------------------------------------------------------------------------
| Student / Instructor Routes (Guard: web)
|--------------------------------------------------------------------------
*/
Route::post('/chatbot/send', [App\Http\Controllers\Admin\ChatbotController::class, 'sendMessage'])->name('chatbot.send');


// Group them for clarity
Route::prefix('payments')->name('payments.')->group(function () {
    // 1. The Checkout Page
    Route::get('/checkout/{course}', [PaymentController::class, 'checkout'])->name('checkout');

    // 2. The Form Submission (Where the "Pay Now" button goes)
    Route::post('/process', [PaymentController::class, 'process'])->name('process');

    // 3. The Success Page (Where Chapa redirects the user)
    Route::get('/success', [PaymentController::class, 'success'])->name('success');

    // 4. The Callback (Where Chapa sends the background verification)
    // Note: Make sure this matches the callback_url you send to Chapa!
    Route::get('/callback/{tx_ref}', [PaymentController::class, 'callback'])->name('callback');
});
Route::middleware(['auth'])->group(function () {
    
    // Dashboards
    Route::get('/dashboard', fn () => Inertia::render('User/Dashboard'))->name('dashboard');
    Route::get('/student/dashboard', fn () => Inertia::render('Student/Dashboard'))->middleware('role:student')->name('student.dashboard');
    Route::get('/instructor/dashboard', fn () => Inertia::render('Instructor/Dashboard'))->middleware('role:instructor')->name('instructor.dashboard');

    // Profile
   Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Chatbot
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
// Move this OUTSIDE of any Route::middleware(['auth']) groups
    // Support Tickets (User Side)
    // Support Tickets (User Side)
Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index'); 
    Route::get('/create', [SupportTicketController::class, 'create'])->name('create'); // This becomes 'tickets.create'
    Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/message', [SupportTicketController::class, 'storeMessage'])->name('message.store');
});

    // --- NEW: Payment & Enrollment Routes ---
    Route::get('/courses/{course}/checkout', [EnrollmentController::class, 'checkout'])->name('courses.checkout');
    Route::post('/courses/{course}/checkout', [EnrollmentController::class, 'initiatePayment'])->name('courses.checkout.process');
    Route::get('/enrollment/callback/{tx_ref}', [EnrollmentController::class, 'callback'])->name('enrollment.callback');
});

/*
|--------------------------------------------------------------------------
| Admin System (Guard: admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest Admin
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
        Route::get('/register', [AdminAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.submit');
    });

    // Authenticated Admin
    Route::middleware('auth:admin')->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    // Store User (The Logic)
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    
    // Edit User (The Form)
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    
    // Update User (The Logic)
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    
    // Delete User
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        // Notifications (Fixed Real-time Center)
        Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/read', 'markAsRead')->name('read');
            Route::post('/mark-all-read', 'markAllAsRead')->name('markAllRead');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        // Reports & Downloads
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/enrollment/download', [PaymentController::class, 'downloadReport'])->name('reports.enrollment.download');

        // LMS Core
        Route::resource('categories', CategoryController::class);
        Route::resource('courses', CourseController::class);

        // Sections & Lessons
        Route::post('/courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('/sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

        // Resources, Progress & Certificates
        Route::post('/lessons/{lesson}/resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
        Route::post('/lessons/{lesson}/progress', [ProgressTrackingController::class, 'update'])->name('progress.update');
        Route::get('/courses/{course}/progress', [ProgressTrackingController::class, 'courseProgress'])->name('progress.course');
        Route::get('/certificates/{courseId}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

        // Quizzes
        Route::post('/sections/{section}/quizzes', [QuizController::class, 'store'])->name('sections.quizzes.store');
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        // Quiz Attempts & Reviews
        Route::get('/attempts', [QuizAttemptController::class, 'index'])->name('attempts.index');
        Route::get('/attempts/{id}', [QuizAttemptController::class, 'show'])->name('attempts.show');
        Route::patch('/answers/{answer}/score', [QuizAttemptController::class, 'updateScore'])->name('answers.update-score');
        
        Route::get('courses/{course}/reviews', [ReviewController::class, 'index'])->name('courses.reviews');
        Route::patch('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');
        Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Support Management (Admin Side)
        Route::controller(AdminSupportTicketController::class)->prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{ticket}', 'show')->name('show');
            Route::patch('/{ticket}/status', 'updateStatus')->name('status.update');
            Route::post('/{ticket}/message', 'storeMessage')->name('message.store');
        });

        // Test Route for Real-time Verification
        Route::get('/test-notify', function() {
            $admin = auth()->user();
            $notification = \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'system',
                'title' => 'Real-time Test',
                'message' => 'Notification received via Reverb!',
                'is_read' => false
            ]);
            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
            return "Event Dispatched! Check your browser.";
        })->name('test-notify');
    });
});

require __DIR__.'/auth.php';