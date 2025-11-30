<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Ticket;
use App\Models\User;

class TicketOwnerMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware này kiểm tra user có phải là owner của ticket không
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

        // Admin có thể access tất cả tickets
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Lấy ticket ID từ route parameter
        $ticketId = $this->getTicketIdFromRequest($request);
        
        if (!$ticketId) {
            return response()->json([
                'message' => 'Ticket ID not found in request.'
            ], 400);
        }

        // Tìm ticket
        $ticket = Ticket::find($ticketId);
        
        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.'
            ], 404);
        }

        // Kiểm tra user có phải là owner của ticket này không
        if ($ticket->attendee_id !== $user->user_id) {
            return response()->json([
                'message' => 'Access denied. You can only access your own tickets.'
            ], 403);
        }

        // Gắn ticket vào request để controller không phải query lại
        $request->merge(['ticket' => $ticket]);

        return $next($request);
    }

    /**
     * Lấy Ticket ID từ request
     */
    private function getTicketIdFromRequest(Request $request): ?int
    {
        $route = $request->route();
        if (!$route) return null;

        // Thử lấy từ các parameter có thể có
        if ($route->hasParameter('ticket')) {
            return (int) $route->parameter('ticket');
        }
        
        if ($route->hasParameter('id')) {
            return (int) $route->parameter('id');
        }
        
        if ($request->has('ticket_id')) {
            return (int) $request->get('ticket_id');
        }

        return null;
    }
}