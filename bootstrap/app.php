<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'organizer' => \App\Http\Middleware\OrganizerMiddleware::class,
            'event.owner' => \App\Http\Middleware\EventOwnerMiddleware::class,
            'event.status' => \App\Http\Middleware\CheckEventStatusMiddleware::class,
            'ticket.owner' => \App\Http\Middleware\TicketOwnerMiddleware::class,
            'payment.verify' => \App\Http\Middleware\PaymentVerificationMiddleware::class,
            'custom.throttle' => \App\Http\Middleware\RateLimitMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
