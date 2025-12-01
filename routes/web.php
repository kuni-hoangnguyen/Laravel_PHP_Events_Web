<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// ================================================================
// PUBLIC ROUTES (Không cần đăng nhập)
// ================================================================

Route::get('/', [WelcomeController::class, 'welcome'])->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::middleware(['auth', 'custom.throttle:3,5'])->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('auth.show-forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
});

// Public events endpoints
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{id}/reviews', [ReviewController::class, 'index'])->name('events.reviews');
Route::get('/events/{id}/ticket-types', [TicketController::class, 'getTicketTypes'])->name('events.ticket-types');

// Helper endpoints
Route::get('/categories', [EventController::class, 'categories'])->name('categories.index');
Route::get('/locations', [EventController::class, 'locations'])->name('locations.index');

// ================================================================
// AUTHENTICATED ROUTES (Cần đăng nhập)
// ================================================================

Route::middleware(['auth'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');

    // User dashboard
    Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.my');
    Route::get('/my-payments', [PaymentController::class, 'index'])->name('payments.my');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    // ================================================================
    // VERIFIED EMAIL REQUIRED ROUTES
    // ================================================================

    Route::middleware(['verified'])->group(function () {
        // Ticket purchasing (with rate limiting)
        Route::middleware(['custom.throttle:10,1', 'event.status:buy_ticket'])->group(function () {
            Route::post('/events/{event}/tickets', [TicketController::class, 'purchase'])->name('tickets.purchase');
        });

        // Reviews (only after event ended)
        Route::middleware(['event.status:review'])->group(function () {
            Route::post('/events/{event}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        });

        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // ================================================================
        // ORGANIZER ROUTES (Admin hoặc Organizer)
        // ================================================================

        Route::middleware(['organizer'])->group(function () {
            Route::post('/events', [EventController::class, 'store'])->name('events.store');
            Route::get('/my-events', [EventController::class, 'myEvents'])->name('events.my');
        });

        // Event owner specific routes
        Route::middleware(['event.owner', 'event.status:edit'])->group(function () {
            Route::put('/my-events/{event}', [EventController::class, 'update'])->name('events.update');
            Route::delete('/my-events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        });
    });

    // ================================================================
    // TICKET OWNER ROUTES
    // ================================================================

    Route::middleware(['ticket.owner'])->group(function () {
        Route::get('/my-tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    });

    // ================================================================
    // PAYMENT VERIFICATION ROUTES
    // ================================================================

    Route::middleware(['payment.verify'])->group(function () {
        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    });

    // ================================================================
    // NOTIFICATION ROUTES (Authenticated users)
    // ================================================================

    Route::prefix('notifications')
        ->name('notifications.')
        ->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread.count');
            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark.read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark.all.read');
        });

    // ================================================================
    // FAVORITE ROUTES (Authenticated users)
    // ================================================================

    Route::prefix('favorites')
        ->name('favorites.')
        ->group(function () {
            Route::get('/', [FavoriteController::class, 'index'])->name('index');
            Route::get('/recommendations', [FavoriteController::class, 'recommendations'])->name('recommendations');
            Route::post('/events/{event}', [FavoriteController::class, 'store'])->name('store');
            Route::delete('/events/{event}', [FavoriteController::class, 'destroy'])->name('destroy');
            Route::post('/events/{event}/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
            Route::get('/events/{event}/check', [FavoriteController::class, 'check'])->name('check');
        });

    // ================================================================
    // QR CODE ROUTES (Authenticated users)
    // ================================================================

    Route::middleware(['auth'])->group(function () {
        Route::get('/ticket/{ticketId}/qr', [App\Http\Controllers\QRCodeController::class, 'getTicketQR'])->name('ticket.qr');
        // QR code check-in routes (event owners only)
        Route::middleware(['event.owner'])->group(function () {
            Route::get('/event/{eventId}/checkin-stats', [App\Http\Controllers\QRCodeController::class, 'getCheckInStats'])->name('event.checkin.stats');
            Route::get('/event/{eventId}/attendees', [App\Http\Controllers\QRCodeController::class, 'getCheckedInAttendees'])->name('event.attendees');
            Route::get('/event/{eventId}/qr-scanner', [App\Http\Controllers\QRCodeController::class, 'showScanner'])->name('event.qr.scanner');
            Route::post('/event/{eventId}/checkin', [App\Http\Controllers\QRCodeController::class, 'checkInByEvent'])->name('event.qr.checkin');
        });
    });
});

// ================================================================
// ADMIN ONLY ROUTES
// ================================================================

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Event management
        Route::get('/events', [AdminController::class, 'events'])->name('events.index');
        Route::post('/events/{event}/approve', [AdminController::class, 'approveEvent'])->name('events.approve');
        Route::post('/events/{event}/reject', [AdminController::class, 'rejectEvent'])->name('events.reject');

        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        // Refund management
        Route::get('/refunds', [AdminController::class, 'refunds'])->name('refunds.index');
        Route::post('/refunds/{refund}/process', [AdminController::class, 'processRefund'])->name('refunds.process');

        // Admin logs
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs.index');
    });

// ================================================================
// SPECIAL ROUTES (Webhook, Check-in, etc.)
// ================================================================

// Ticket check-in (có thể được gọi bởi staff tại event)
Route::post('/tickets/{ticket}/check-in', [TicketController::class, 'checkIn'])->name('tickets.check-in');

// Webhook routes (từ payment gateway - không cần auth)
// Route::post('/webhooks/payment', [PaymentController::class, 'webhook'])->name('webhooks.payment');
