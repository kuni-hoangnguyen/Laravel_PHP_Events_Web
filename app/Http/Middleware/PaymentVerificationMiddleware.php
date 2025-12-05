<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;

class PaymentVerificationMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware này xác thực payment và kiểm tra tính hợp lệ
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        $paymentId = $this->getPaymentIdFromRequest($request);
        
        if (!$paymentId) {
            return redirect()->back()->with('error', 'Không tìm thấy Payment ID trong request.');
        }

        $payment = Payment::with(['ticket.attendee'])->find($paymentId);
        
        if (!$payment) {
            return redirect()->back()->with('error', 'Không tìm thấy payment.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            if ($payment->ticket->attendee_id !== $user->user_id) {
                return redirect()->back()->with('warning', 'Bạn chỉ có thể truy cập payment của mình.');
            }
        }

        $action = $this->getActionFromRequest($request);
        
        switch ($action) {
            case 'refund':
                if (!$payment->canRefund()) {
                    return redirect()->back()->with('warning', 'Payment này không thể refund. Có thể đã refund hoặc thất bại.');
                }
                break;
                
            case 'confirm':
                if ($payment->status !== 'pending') {
                    return redirect()->back()->with('warning', 'Payment không ở trạng thái chờ xác nhận.');
                }
                break;
        }

        $request->merge(['payment' => $payment]);

        return $next($request);
    }

    /**
     * Lấy action từ request
     */
    private function getActionFromRequest(Request $request): string
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $path = $request->path();
        
        if (str_contains($routeName, 'refund') || str_contains($path, 'refund')) {
            return 'refund';
        }
        
        if (str_contains($routeName, 'confirm') || str_contains($path, 'confirm')) {
            return 'confirm';
        }
        
        return 'unknown';
    }

    /**
     * Lấy Payment ID từ request
     */
    private function getPaymentIdFromRequest(Request $request): ?int
    {
        $route = $request->route();
        if (!$route) return null;

        if ($route->hasParameter('payment')) {
            return (int) $route->parameter('payment');
        }
        
        if ($route->hasParameter('id')) {
            return (int) $route->parameter('id');
        }
        
        if ($request->has('payment_id')) {
            return (int) $request->get('payment_id');
        }

        return null;
    }
}