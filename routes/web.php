<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\User\SupportTicketController; // Add this
use App\Http\Controllers\Admin\AdminSupportTicketController; // Add this
use App\Http\Controllers\Admin\UserController; // Add this
use App\Http\Controllers\Admin\DashboardController; // Add this
use App\Http\Controllers\HomeController; // Add this
use App\Http\Controllers\ChatbotController; // Add this
use App\Http\Controllers\Admin\{
    CategoryController, CourseController, SectionController, 
    LessonController, ResourceController, ProgressTrackingController, 
    CertificateController, QuizController, QuestionController, 
    AttemptReviewController, ReviewController, QuizAttemptController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
//Route::get('/', fn () => Inertia::render('Home'))->name('home');
// The Welcome Page (Managed by HomeController)
Route::get('/', [HomeController::class, 'index'])->name('home');

// The Chat Logic (Managed by ChatbotController)
// Route::middleware(['auth'])->group(function () {
//     Route::post('/chatbot/send/{session}', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
// });
Route::post('/chatbot/send/{session}', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes (Students & Instructors)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Dashboards by Role
    Route::get('/dashboard', fn () => Inertia::render('User/Dashboard'))->name('dashboard');
    Route::get('/student/dashboard', fn () => Inertia::render('Student/Dashboard'))->middleware('role:student')->name('student.dashboard');
    Route::get('/instructor/dashboard', fn () => Inertia::render('Instructor/Dashboard'))->middleware('role:instructor')->name('instructor.dashboard');

    // Profile Management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Notifications API & Pages
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/json', 'getNotificationsJson')->name('json');
        Route::get('/unread-count', 'getUnreadCount')->name('unread-count');
        Route::post('/{id}/read', 'markAsRead')->name('mark-read');
        Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin System (Prefix: admin / Name: admin.)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest Admin Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
        Route::get('/register', [AdminAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.submit');
    });

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index']);

        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        // LMS Core Resources
        Route::resource('categories', CategoryController::class);
        Route::resource('courses', CourseController::class);

        // Sections & Lessons
        Route::post('/courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('/sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

        // Resources & Progress
        Route::post('/lessons/{lesson}/resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
        Route::post('/lessons/{lesson}/progress', [ProgressTrackingController::class, 'update'])->name('progress.update');
        Route::get('/courses/{course}/progress', [ProgressTrackingController::class, 'courseProgress'])->name('progress.course');
        //Route::get('/certificates/{courseId}', [CertificateController::class, 'show'])->name('certificates.show');
        //Route::get('/certificates/{id}/download', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('/certificates/{courseId}', [CertificateController::class, 'show'])->name('certificates.show');
        
        // This takes a Certificate ID (to download the specific record)
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
        // Quizzes & Questions
        Route::post('/sections/{section}/quizzes', [QuizController::class, 'store'])->name('sections.quizzes.store');
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('admin.quizzes.questions.store');
    
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        // Reviews & Grading
        Route::get('courses/{course}/reviews', [ReviewController::class, 'index'])->name('courses.reviews');
        Route::patch('reviews/{review}/toggle', [ReviewController::class, 'toggleApproval'])->name('reviews.toggle');
        Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Quiz Attempts
        Route::get('/attempts', [QuizAttemptController::class, 'index'])->name('attempts.index');
        Route::get('/attempts/{id}', [QuizAttemptController::class, 'show'])->name('attempts.show');
        Route::patch('/answers/{answer}/score', [QuizAttemptController::class, 'updateScore'])->name('answers.update-score');
    });
});
Route::middleware(['auth'])->group(function () {
    
    // ... (Existing Dashboards & Profile routes) ...

    // Support Tickets (User Side)
    Route::controller(SupportTicketController::class)->prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', 'index')->name('index');           // List my tickets
        Route::post('/', 'store')->name('store');          // Open a new ticket
        Route::get('/{ticket}', 'show')->name('show');     // View ticket & messages
        Route::post('/{ticket}/message', 'storeMessage')->name('message.store'); // Reply to ticket
    });

    // ... (Existing Notifications) ...
});

Route::middleware(['auth'])->group(function () {
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/send/{session}', [ChatbotController::class, 'sendMessage'])->name('chatbot.send');
});

/*
|--------------------------------------------------------------------------
| Admin System
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {


    // ... (Guest Admin Routes) ...

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        
        // ... (Existing Admin Dashboard, Courses, etc) ...

        // Support Management (Admin Side)
        Route::controller(AdminSupportTicketController::class)->prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', 'index')->name('index');               // See ALL user tickets
            Route::get('/{ticket}', 'show')->name('show');         // View a user's ticket
            Route::patch('/{ticket}/status', 'updateStatus')->name('status.update'); // Change status (resolved/closed)
            Route::post('/{ticket}/message', 'storeMessage')->name('message.store'); // Admin reply
        });

        // ... (Existing Reviews, Quizzes, etc) ...
    });
});

require __DIR__.'/auth.php';