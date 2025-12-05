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
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ticketId = $this->getTicketIdFromRequest($request);
        
        if (!$ticketId) {
            return redirect()->back()->with('error', 'Không tìm thấy Ticket ID trong request.');
        }

        $ticket = Ticket::where('ticket_id', $ticketId)->first();
        
        if (!$ticket) {
            return redirect()->back()->with('error', 'Không tìm thấy vé.');
        }

        if (!$user->isAdmin()) {
            if ($ticket->attendee_id !== $user->user_id) {
                return redirect()->back()->with('warning', 'Bạn chỉ có thể truy cập vé của mình.');
            }
        }

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