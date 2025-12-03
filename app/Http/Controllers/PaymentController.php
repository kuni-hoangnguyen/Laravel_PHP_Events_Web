<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Refund;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends WelcomeController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Lấy danh sách payments của user
     */
    public function index()
    {
        $user = Auth::user();
        $query = Payment::with(['ticket.attendee', 'ticket.ticketType.event', 'paymentMethod']);
        
        // Admin có thể xem tất cả payments, user thường chỉ xem payments của mình
        if (!$user->isAdmin()) {
            $query->whereHas('ticket', function($q) use ($user) {
                $q->where('attendee_id', $user->user_id);
            });
        }
        
        $payments = $query->orderBy('payment_id', 'desc')
                          ->paginate(12);

        return view('payments.index', compact('payments'));
    }

    /**
     * Confirm payment (webhook từ payment gateway)
     */
    public function confirm(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'transaction_reference' => 'required|string',
            'gateway_response' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu xác nhận thanh toán không hợp lệ!');
        }
        try {
            $payment = Payment::where('payment_id', $paymentId)->firstOrFail();
            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
                'transaction_id' => $request->transaction_reference,
            ]);
            
            // Update ticket payment status
            if ($payment->ticket) {
                $payment->ticket->update(['payment_status' => 'paid']);
            }

            return redirect()->back()->with('success', 'Xác nhận thanh toán thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xác nhận thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Request refund
     */
    public function refund(Request $request, $paymentId)
    {
        $payment = $request->payment; // Từ middleware payment.verify

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu yêu cầu hoàn tiền không hợp lệ!');
        }

        // Kiểm tra đã có refund request chưa
        $existingRefund = Refund::where('payment_id', $paymentId)->first();
        if ($existingRefund) {
            return redirect()->back()->with('warning', 'Yêu cầu hoàn tiền đã tồn tại!');
        }

        $refund = Refund::create([
            'payment_id' => $paymentId,
            'requester_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'pending', // Refund status có 'pending' trong ENUM
        ]);

        // Gửi notification cho admin và organizer
        try {
            $payment->load(['ticket.ticketType.event.organizer', 'ticket.attendee']);
            $event = $payment->ticket->ticketType->event;
            $requester = $payment->ticket->attendee;
            $eventName = $event->title ?? $event->event_name;
            
            // Thông báo cho admin
            $this->notificationService->notifyAdminRefundRequest(
                $refund->refund_id,
                $eventName,
                $requester->full_name ?? $requester->name,
                $payment->amount,
                $request->reason
            );

            // Thông báo cho organizer (nếu có)
            if ($event->organizer_id) {
                $this->notificationService->notifyOrganizerRefundRequest(
                    $event->organizer_id,
                    $eventName,
                    $requester->full_name ?? $requester->name,
                    $payment->amount,
                    $request->reason,
                    $event->event_id
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admin/organizer about refund request', [
                'error' => $e->getMessage(),
                'refund_id' => $refund->refund_id,
            ]);
        }

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được gửi!');
    }
}