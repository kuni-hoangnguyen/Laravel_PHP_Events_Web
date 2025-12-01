<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends WelcomeController
{
    /**
     * Lấy danh sách payments của user
     */
    public function index()
    {
        $payments = Payment::with(['ticket.attendee', 'ticket.ticketType.event', 'paymentMethod'])
                          ->whereHas('ticket', function($query) {
                              $query->where('attendee_id', Auth::id());
                          })
                          ->orderBy('paid_at', 'desc')
                          ->paginate(12);

        return view('payments.index', compact('payments'));
    }

    /**
     * Xem chi tiết payment
     */
    public function show($paymentId)
    {
        $payment = Payment::with(['ticket.attendee', 'ticket.ticketType.event', 'paymentMethod'])
                         ->where('payment_id', $paymentId)
                         ->whereHas('ticket', function($query) {
                             $query->where('attendee_id', Auth::id());
                         })
                         ->firstOrFail();

        return view('payments.show', compact('payment'));
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
            $payment = Payment::findOrFail($paymentId);
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
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được gửi!');
    }
}