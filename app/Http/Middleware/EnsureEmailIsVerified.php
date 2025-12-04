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
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Bạn cần đăng nhập để tiếp tục.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->email_verified_at) {
            return redirect()->route('home')->with('error', 'Bạn cần xác thực email để sử dụng chức năng này.');
        }

        return $next($request);
    }
}
