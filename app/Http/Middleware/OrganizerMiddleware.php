<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizerMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware này kiểm tra user có quyền tạo/quản lý events không
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('home')->with('error', 'Bạn cần đăng nhập để truy cập chức năng này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->canCreateEvent()) {
            return redirect()->route('home')->with('error', 'Bạn cần có vai trò Organizer hoặc Admin để truy cập trang này.');
        }

        return $next($request);
    }
}
