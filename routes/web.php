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
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Password reset routes (không cần auth)
Route::middleware(['custom.throttle:3,5'])->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
});

// Password reset form (signed URL)
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
    ->middleware(['signed'])
    ->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Public events endpoints
// Đặt route cụ thể trước route có parameter để tránh conflict
Route::get('/events', [EventController::class, 'index'])->name('events.index');
// Route /events/my và /events/create phải được đặt trước /events/{id} để tránh conflict
Route::middleware(['auth', 'organizer'])->group(function () {
    Route::get('/events/my', [EventController::class, 'myEvents'])->name('events.my');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
});
Route::get('/events/{id}/reviews', [ReviewController::class, 'index'])->name('events.reviews');
Route::get('/events/{id}/ticket-types', [TicketController::class, 'getTicketTypes'])->name('events.ticket-types');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

// Helper endpoints
Route::get('/categories', [EventController::class, 'categories'])->name('categories.index');
Route::get('/locations', [EventController::class, 'locations'])->name('locations.index');

// ================================================================
// AUTHENTICATED ROUTES (Cần đăng nhập)
// ================================================================

// Email verification route (public, uses signed URL)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware(['auth'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::match(['get', 'put'], '/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('custom.throttle:3,1')
        ->name('verification.send');

    // User dashboard
    Route::get('/tickets', [TicketController::class, 'myTickets'])->name('tickets.index');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    // ================================================================
    // VERIFIED EMAIL REQUIRED ROUTES
    // ================================================================

    Route::middleware(['verified'])->group(function () {
        // Ticket purchasing (with rate limiting)
        Route::middleware(['custom.throttle:10,1', 'event.status:buy_ticket'])->group(function () {
            Route::get('/events/{event}/purchase', [TicketController::class, 'showPurchaseForm'])->name('tickets.purchase');
            Route::post('/events/{event}/tickets', [TicketController::class, 'purchase'])->name('tickets.store');
        });

        // Reviews (only after event ended)
        Route::middleware(['event.status:review'])->group(function () {
            Route::get('/events/{event}/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
            Route::post('/events/{event}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        });

        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // ================================================================
        // ORGANIZER ROUTES (Admin hoặc Organizer)
        // ================================================================

        Route::middleware(['organizer'])->group(function () {
            Route::get('/organizer/dashboard', [EventController::class, 'dashboard'])->name('organizer.dashboard');
            Route::post('/events', [EventController::class, 'store'])->name('events.store');
        });

        // Event owner specific routes
        Route::middleware(['event.owner', 'event.status:edit'])->group(function () {
            Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
            Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
            Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        });

        // Cash payment confirmation routes (event owner)
        Route::middleware(['event.owner'])->group(function () {
            Route::get('/events/{event}/pending-payments', [EventController::class, 'pendingCashPayments'])->name('events.pending-payments');
            Route::post('/payments/{payment}/confirm-cash', [EventController::class, 'confirmCashPayment'])->name('payments.confirm-cash');
            Route::post('/payments/{payment}/reject-cash', [EventController::class, 'rejectCashPayment'])->name('payments.reject-cash');
            Route::post('/events/{event}/request-cancellation', [EventController::class, 'requestCancellation'])->name('events.request-cancellation');
        });
    });

    // ================================================================
    // TICKET OWNER ROUTES
    // ================================================================

    Route::middleware(['ticket.owner'])->group(function () {
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
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
            Route::get('/{notification}/read-and-redirect', [NotificationController::class, 'readAndRedirect'])->name('read.and.redirect');
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

    // QR code routes
    Route::get('/tickets/{ticket}/qr', [App\Http\Controllers\QRCodeController::class, 'getTicketQR'])->name('tickets.qr');

    // QR code check-in routes (event owners only)
    Route::middleware(['event.owner'])->group(function () {
        Route::get('/events/{event}/checkin/stats', [App\Http\Controllers\QRCodeController::class, 'getCheckInStats'])->name('events.checkin.stats');
        Route::get('/events/{event}/checkin/attendees', [App\Http\Controllers\QRCodeController::class, 'getCheckedInAttendees'])->name('events.checkin.attendees');
        Route::get('/events/{event}/checkin/scanner', [App\Http\Controllers\QRCodeController::class, 'showScanner'])->name('events.checkin.scanner');
        Route::post('/events/{event}/checkin', [App\Http\Controllers\QRCodeController::class, 'checkInByEvent'])->name('events.checkin');
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
        Route::post('/events/{event}/approve-cancellation', [AdminController::class, 'approveCancellation'])->name('events.approve-cancellation');
        Route::post('/events/{event}/reject-cancellation', [AdminController::class, 'rejectCancellation'])->name('events.reject-cancellation');
        Route::delete('/events/{event}', [AdminController::class, 'deleteEvent'])->name('events.delete');

        // User management
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        // Refund management
        Route::get('/refunds', [AdminController::class, 'refunds'])->name('refunds.index');
        Route::post('/refunds/{refund}/process', [AdminController::class, 'processRefund'])->name('refunds.process');

        // Admin logs
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs.index');
        Route::get('/logs/{log}', [AdminController::class, 'showLog'])->name('logs.show');

        // Payment management
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments.index');

        // Ticket management
        Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets.index');

        // Category management
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [AdminController::class, 'createCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');

        // Location management
        Route::get('/locations', [AdminController::class, 'locations'])->name('locations.index');
        Route::post('/locations', [AdminController::class, 'createLocation'])->name('locations.store');
        Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('locations.destroy');
    });

// ================================================================
// SPECIAL ROUTES (Webhook, Check-in, etc.)
// ================================================================

// Ticket check-in (có thể được gọi bởi staff tại event)
Route::post('/tickets/{ticket}/check-in', [TicketController::class, 'checkIn'])->name('tickets.check-in');

// Webhook routes (từ payment gateway - không cần auth)
// Route::post('/webhooks/payment', [PaymentController::class, 'webhook'])->name('webhooks.payment');
