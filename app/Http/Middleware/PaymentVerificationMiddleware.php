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
        // Kiểm tra user đã đăng nhập chưa
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        // Lấy payment ID từ request
        $paymentId = $this->getPaymentIdFromRequest($request);
        
        if (!$paymentId) {
            return response()->json([
                'message' => 'Payment ID not found in request.'
            ], 400);
        }

        // Tìm payment
        $payment = Payment::with(['ticket.attendee'])->find($paymentId);
        
        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found.'
            ], 404);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin có thể access tất cả payments
        if (!$user->isAdmin()) {
            // Kiểm tra user có phải là owner của ticket này không
            if ($payment->ticket->attendee_id !== $user->user_id) {
                return response()->json([
                    'message' => 'Access denied. You can only access your own payments.'
                ], 403);
            }
        }

        // Kiểm tra tính hợp lệ của payment theo action
        $action = $this->getActionFromRequest($request);
        
        switch ($action) {
            case 'refund':
                if (!$payment->canRefund()) {
                    return response()->json([
                        'message' => 'This payment cannot be refunded. It may already be refunded or failed.'
                    ], 422);
                }
                break;
                
            case 'confirm':
                if ($payment->status !== 'pending') {
                    return response()->json([
                        'message' => 'Payment is not in pending status.'
                    ], 422);
                }
                break;
        }

        // Gắn payment vào request để controller không phải query lại
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

        // Thử lấy từ các parameter có thể có
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
