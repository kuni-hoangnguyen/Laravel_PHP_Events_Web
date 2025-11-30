<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     * Middleware này đảm bảo user đã verify email trước khi thực hiện các hành động quan trọng
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Kiểm tra email đã được verify chưa
        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Your email address is not verified. Please verify your email before continuing.',
                'require_email_verification' => true
            ], 403);
        }

        return $next($request);
    }
}
