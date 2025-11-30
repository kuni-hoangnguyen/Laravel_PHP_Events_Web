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
            return response()->json([
                'message' => 'Unauthorized. Please login first.',
            ], 401);
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
            return response()->json([
                'message' => 'Event ID not found in request.',
            ], 400);
        }

        // Tìm event
        $event = Event::find($eventId);

        if (! $event) {
            return response()->json([
                'message' => 'Event not found.',
            ], 404);
        }

        // Kiểm tra user có phải là organizer của event này không
        if ($event->organizer_id !== $user->user_id) {
            return response()->json([
                'message' => 'Access denied. You can only manage your own events.',
            ], 403);
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

        // Route parameter trực tiếp
        if ($route->hasParameter('event')) {
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
