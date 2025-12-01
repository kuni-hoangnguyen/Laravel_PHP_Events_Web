<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EventOwnerMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware này kiểm tra user có phải là owner của event không
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin có thể access tất cả events
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Lấy event ID từ route parameter
        $eventId = $this->getEventIdFromRequest($request);

        if (! $eventId) {
            return redirect()->back()->with('error', 'Không tìm thấy Event ID trong request.');
        }

        // Tìm event
        $event = Event::find($eventId);

        if (! $event) {
            return redirect()->back()->with('error', 'Không tìm thấy sự kiện.');
        }

        // Kiểm tra user có phải là organizer của event này không
        if ($event->organizer_id !== $user->user_id) {
            return redirect()->back()->with('warning', 'Bạn chỉ có thể quản lý sự kiện của mình.');
        }

        // Gắn event vào request để controller không phải query lại
        $request->merge(['event' => $event]);

        return $next($request);
    }

    /**
     * Lấy Event ID từ request
     */
    private function getEventIdFromRequest(Request $request): ?int
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        // Thử lấy từ các parameter có thể có
        $eventId = null;

        // Ưu tiên lấy từ 'eventId' (đúng với route QR code)
        if ($route->hasParameter('eventId')) {
            $eventId = $route->parameter('eventId');
        }
        // Hoặc từ 'event' (dùng cho các route khác)
        elseif ($route->hasParameter('event')) {
            $eventId = $route->parameter('event');
        }
        // Hoặc từ 'id' parameter
        elseif ($route->hasParameter('id')) {
            $eventId = $route->parameter('id');
        }
        // Hoặc từ request body
        elseif ($request->has('event_id')) {
            $eventId = $request->get('event_id');
        }

        return $eventId ? (int) $eventId : null;
    }
}
