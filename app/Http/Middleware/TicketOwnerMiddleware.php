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
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Lấy ticket ID từ route parameter
        $ticketId = $this->getTicketIdFromRequest($request);
        
        if (!$ticketId) {
            return redirect()->back()->with('error', 'Không tìm thấy Ticket ID trong request.');
        }

        // Tìm ticket
        $ticket = Ticket::where('ticket_id', $ticketId)->first();
        
        if (!$ticket) {
            return redirect()->back()->with('error', 'Không tìm thấy vé.');
        }

        // Admin có thể access tất cả tickets, nhưng vẫn cần kiểm tra owner cho user thường
        if (!$user->isAdmin()) {
        // Kiểm tra user có phải là owner của ticket này không
        if ($ticket->attendee_id !== $user->user_id) {
            return redirect()->back()->with('warning', 'Bạn chỉ có thể truy cập vé của mình.');
            }
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