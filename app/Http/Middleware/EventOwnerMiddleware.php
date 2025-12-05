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
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        $eventId = $this->getEventIdFromRequest($request);

        if (! $eventId) {
            return redirect()->back()->with('error', 'Không tìm thấy Event ID trong request.');
        }

        $event = Event::find($eventId);

        if (! $event) {
            return redirect()->back()->with('error', 'Không tìm thấy sự kiện.');
        }

        if ($event->organizer_id !== $user->user_id) {
            return redirect()->back()->with('warning', 'Bạn chỉ có thể quản lý sự kiện của mình.');
        }

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

        $eventId = null;

        if ($route->hasParameter('eventId')) {
            $eventId = $route->parameter('eventId');
        }
        elseif ($route->hasParameter('event')) {
            $eventId = $route->parameter('event');
        }
        elseif ($route->hasParameter('id')) {
            $eventId = $route->parameter('id');
        }
        elseif ($request->has('event_id')) {
            $eventId = $request->get('event_id');
        }
        elseif ($route->hasParameter('payment')) {
            $payment = $route->parameter('payment');
            if (is_object($payment) && method_exists($payment, 'ticket')) {
                $payment->load('ticket.ticketType');
                if ($payment->ticket && $payment->ticket->ticketType) {
                    $eventId = $payment->ticket->ticketType->event_id;
                }
            }
            elseif (is_numeric($payment)) {
                $paymentModel = \App\Models\Payment::with('ticket.ticketType')
                    ->where('payment_id', $payment)
                    ->first();
                if ($paymentModel && $paymentModel->ticket && $paymentModel->ticket->ticketType) {
                    $eventId = $paymentModel->ticket->ticketType->event_id;
                }
            }
        }

        return $eventId ? (int) $eventId : null;
    }
}
