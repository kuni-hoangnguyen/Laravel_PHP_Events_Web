<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\NotificationService;
use App\Services\PayOSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends WelcomeController
{
    protected $notificationService;

    protected $payOSService;

    public function __construct(NotificationService $notificationService, PayOSService $payOSService)
    {
        $this->notificationService = $notificationService;
        $this->payOSService = $payOSService;
    }

    /**
     * Lấy danh sách payments của user
     */
    public function index()
    {
        $user = Auth::user();
        $query = Payment::with(['ticket.attendee', 'ticket.ticketType.event', 'paymentMethod']);

        if (! $user->isAdmin()) {
            $query->whereHas('ticket', function ($q) use ($user) {
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

            if ($payment->ticket) {
                $payment->ticket->update(['payment_status' => 'paid']);
            }

            return redirect()->back()->with('success', 'Xác nhận thanh toán thành công!');
        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xác nhận thanh toán. Vui lòng thử lại sau.');
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

        $existingRefund = Refund::where('payment_id', $paymentId)->first();
        if ($existingRefund) {
            return redirect()->back()->with('warning', 'Yêu cầu hoàn tiền đã tồn tại!');
        }

        $refund = Refund::create([
            'payment_id' => $paymentId,
            'requester_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        try {
            $payment->load(['ticket.ticketType.event.organizer', 'ticket.attendee']);
            $event = $payment->ticket->ticketType->event;
            $requester = $payment->ticket->attendee;
            $eventName = $event->title ?? $event->event_name;

            $this->notificationService->notifyAdminRefundRequest(
                $refund->refund_id,
                $eventName,
                $requester->full_name ?? $requester->name,
                $payment->amount,
                $request->reason
            );

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

    /**
     * Xử lý return URL từ PayOS sau khi thanh toán
     */
    public function payOSReturn(Request $request, Payment $payment)
    {
        try {
            if ($payment->ticket->attendee_id !== Auth::id()) {
                return redirect()->route('tickets.index')->with('error', 'Không có quyền truy cập.');
            }

            if ($payment->status === 'success') {
                return redirect()->route('tickets.index')->with('success', 'Thanh toán thành công!');
            }

            if ($payment->transaction_id) {
                $orderCode = (int) $payment->transaction_id;
                $paymentInfo = $this->payOSService->getPaymentInfo($orderCode);

                Log::info('PayOS return - checking payment info', [
                    'order_code' => $orderCode,
                    'payment_info' => LogHelper::sanitizePaymentInfo($paymentInfo),
                    'payment_id' => $payment->payment_id,
                    'current_payment_status' => $payment->status,
                    'current_ticket_status' => $payment->ticket->payment_status,
                ]);

                $payment->refresh();
                if ($payment->status === 'success') {
                    return redirect()->route('tickets.index')->with('success', 'Thanh toán thành công!');
                }

                if ($paymentInfo && ($paymentInfo['status'] ?? null) === 'PAID') {
                    DB::beginTransaction();
                    try {
                        $payment->refresh();

                        if ($payment->status !== 'success') {
                            $payment->update([
                                'status' => 'success',
                                'paid_at' => now(),
                            ]);

                            $payment->ticket->refresh();
                            $payment->ticket->update([
                                'payment_status' => 'paid',
                            ]);

                            Log::info('PayOS return - updated payment and ticket', [
                                'payment_id' => $payment->payment_id,
                                'ticket_id' => $payment->ticket_id,
                            ]);
                        }

                        DB::commit();

                        try {
                            $event = $payment->ticket->ticketType->event;
                            $this->notificationService->notifyTicketPurchased(
                                $payment->ticket->attendee_id,
                                $event->title ?? $event->event_name,
                                $payment->ticket->quantity,
                                $payment->amount
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send ticket purchase success notification', ['error' => $e->getMessage()]);
                        }

                        return redirect()->route('tickets.index')->with('success', 'Thanh toán thành công!');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('PayOS return - update failed', [
                            'error' => $e->getMessage(),
                            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                            'payment_id' => $payment->payment_id,
                        ]);
                        throw $e;
                    }
                } else {
                    Log::warning('PayOS return - payment info not available', [
                        'order_code' => $orderCode,
                        'status' => $paymentInfo['status'] ?? 'unknown',
                        'payment_id' => $payment->payment_id,
                    ]);

                    $payment->refresh();

                    if ($payment->created_at) {
                        $minutesSinceCreated = $payment->created_at->diffInMinutes(now());
                        $paymentMethod = $payment->paymentMethod;
                        $isNonCash = $paymentMethod && $paymentMethod->name !== 'Tiền mặt';

                        if ($minutesSinceCreated >= 10 && $isNonCash) {
                            if ($payment->status === 'failed' && $payment->ticket->payment_status === 'pending') {
                                DB::beginTransaction();
                                try {
                                    $ticket = $payment->ticket;
                                    $ticketType = $ticket->ticketType;

                                    $ticket->update([
                                        'payment_status' => 'cancelled',
                                    ]);

                                    if ($ticketType) {
                                        $ticketType->increment('remaining_quantity', $ticket->quantity ?? 1);
                                    }

                                    DB::commit();

                                    Log::info('Non-cash payment expired on return', [
                                        'payment_id' => $payment->payment_id,
                                        'ticket_id' => $ticket->ticket_id,
                                        'method' => $paymentMethod->name ?? 'Unknown',
                                        'minutes_since_created' => $minutesSinceCreated,
                                    ]);

                                    return redirect()->route('tickets.index')->with('error', 'Thanh toán đã hết hạn (quá 10 phút). Vé đã được hủy và số lượng vé đã được hoàn lại.');
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    Log::error('Payment return - expire payment failed', ['error' => $e->getMessage()]);
                                }
                            }

                            return redirect()->route('tickets.index')->with('error', 'Thanh toán đã hết hạn. Vui lòng mua lại vé.');
                        } elseif ($minutesSinceCreated >= 1 && $payment->status !== 'success' && $isNonCash) {
                            DB::beginTransaction();
                            try {
                                $payment->update([
                                    'status' => 'success',
                                    'paid_at' => now(),
                                ]);

                                $payment->ticket->refresh();
                                $payment->ticket->update([
                                    'payment_status' => 'paid',
                                ]);

                                DB::commit();

                                return redirect()->route('tickets.index')->with('success', 'Thanh toán thành công!');
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::error('PayOS return - fallback update failed', ['error' => $e->getMessage()]);
                            }
                        }
                    }
                }
            }

            return redirect()->route('tickets.index')->with('warning', 'Thanh toán chưa hoàn tất. Vui lòng kiểm tra lại hoặc đợi vài phút.');
        } catch (\Exception $e) {
            Log::error('PayOS return error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'payment_id' => $payment->payment_id ?? null,
            ]);

            return redirect()->route('tickets.index')->with('error', 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng liên hệ hỗ trợ.');
        }
    }

    /**
     * Xử lý cancel URL từ PayOS khi người dùng hủy thanh toán
     */
    public function payOSCancel(Request $request, Payment $payment)
    {
        return redirect()->route('tickets.index')->with('info', 'Bạn đã hủy thanh toán.');
    }

    /**
     * Xử lý webhook từ PayOS
     */
    public function payOSWebhook(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('PayOS webhook received', [
                'data' => LogHelper::sanitize($data),
                'headers' => LogHelper::sanitizeHeaders($request->headers->all()),
            ]);

            $payment = $this->payOSService->handleCallback($data);

            if ($payment && $payment->status === 'success') {
                try {
                    $event = $payment->ticket->ticketType->event;
                    $this->notificationService->notifyTicketPurchased(
                        $payment->ticket->attendee_id,
                        $event->title ?? $event->event_name,
                        $payment->ticket->quantity,
                        $payment->amount
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send ticket purchase success notification', ['error' => $e->getMessage()]);
                }

                return response()->json(['success' => true], 200);
            }

            return response()->json(['success' => false, 'message' => 'Payment not processed'], 200);
        } catch (\Exception $e) {
            Log::error('PayOS webhook error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'data' => LogHelper::sanitize($request->all()),
            ]);

            return response()->json(['error' => 'Payment processing failed'], 200);
        }
    }
}
