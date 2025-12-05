<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\NotificationService;
use App\Services\PayOSService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TicketController extends WelcomeController
{
    protected NotificationService $notificationService;

    protected QRCodeService $qrCodeService;

    protected PayOSService $payOSService;

    public function __construct(NotificationService $notificationService, QRCodeService $qrCodeService, PayOSService $payOSService)
    {
        $this->notificationService = $notificationService;
        $this->qrCodeService = $qrCodeService;
        $this->payOSService = $payOSService;
    }

    /**
     * Hiển thị form mua vé
     */
    public function showPurchaseForm($eventId)
    {
        $event = Event::with(['ticketTypes' => function ($query) {
            $query->where('is_active', true)
                ->where('remaining_quantity', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('sale_start_time')
                        ->orWhere('sale_start_time', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('sale_end_time')
                        ->orWhere('sale_end_time', '>=', now());
                });
        }, 'category', 'location'])->where('event_id', $eventId)->firstOrFail();

        return view('tickets.purchase', compact('event'));
    }

    /**
     * Mua vé cho event
     */
    public function purchase(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'ticket_type_id' => 'required|exists:ticket_types,ticket_type_id',
            'quantity' => 'required|integer|min:1|max:10',
            'payment_method_id' => 'required|exists:payment_methods,method_id',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu mua vé không hợp lệ!');
        }
        $event = Event::where('event_id', $eventId)->firstOrFail();

        $paymentMethod = \App\Models\PaymentMethod::where('method_id', $request->payment_method_id)->firstOrFail();
        $isCashPayment = stripos($paymentMethod->name, 'tiền mặt') !== false || stripos($paymentMethod->name, 'cash') !== false;
        $isPayOS = stripos($paymentMethod->name, 'payos') !== false || stripos($paymentMethod->name, 'payos') !== false;

        try {
            DB::beginTransaction();

            $ticketType = TicketType::where('ticket_type_id', $request->ticket_type_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($ticketType->remaining_quantity < $request->quantity) {
                DB::rollBack();

                return redirect()->back()->with('warning', 'Số lượng vé không đủ! Vui lòng thử lại.');
            }

            $updated = DB::table('ticket_types')
                ->where('ticket_type_id', $request->ticket_type_id)
                ->where('remaining_quantity', '>=', $request->quantity)
                ->decrement('remaining_quantity', $request->quantity);

            if ($updated === 0) {
                DB::rollBack();

                return redirect()->back()->with('warning', 'Số lượng vé không đủ! Có thể đã có người khác mua trước đó.');
            }

            $ticketType->refresh();

            $totalAmount = $ticketType->price * $request->quantity;
            
            $ticket = Ticket::create([
                'ticket_type_id' => $request->ticket_type_id,
                'attendee_id' => Auth::id(),
                'quantity' => $request->quantity,
                'payment_status' => 'pending',
                'purchase_time' => now(),
            ]);

            $this->qrCodeService->generateQRCode($ticket);

            if ($isCashPayment) {
                $payment = Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'amount' => $totalAmount,
                    'method_id' => $request->payment_method_id,
                    'status' => 'failed',
                    'transaction_id' => 'CASH_'.time().'_'.Auth::id(),
                    'paid_at' => null,
                ]);
            } elseif ($isPayOS) {
                $payment = Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'amount' => $totalAmount,
                    'method_id' => $request->payment_method_id,
                    'status' => 'failed',
                    'transaction_id' => 'PAYOS_'.time().'_'.Auth::id(),
                    'paid_at' => null,
                ]);
            } else {
                DB::rollBack();

                return redirect()->back()->with('error', 'Phương thức thanh toán này chưa được hỗ trợ.');
            }

            $ticket->refresh();
            $ticket->load('payment');

            $event = Event::where('event_id', $eventId)->first();
            if ($isCashPayment) {
                $this->notificationService->notifyCashPaymentPending(
                    $event->organizer_id,
                    Auth::user()->full_name,
                    $event->event_name ?? $event->title,
                    $eventId,
                    $request->quantity,
                    $totalAmount
                );
            }

            DB::commit();

            try {
                AdminLog::logUserAction(null, 'purchase_tickets', 'tickets', $ticket->ticket_id, null, [
                    'event_id' => $eventId,
                    'ticket_type_id' => $request->ticket_type_id,
                    'quantity' => $request->quantity,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod->name,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log purchase tickets action', ['error' => $e->getMessage()]);
            }

            if ($isPayOS) {
                try {
                    $returnUrl = url(route('payments.payos.return', ['payment' => $payment->payment_id], false));
                    $cancelUrl = url(route('payments.payos.cancel', ['payment' => $payment->payment_id], false));
                    
                    $paymentLink = $this->payOSService->createPaymentLink($ticket, $payment, $returnUrl, $cancelUrl);
                    
                    return redirect($paymentLink['checkout_url']);
                } catch (\Exception $e) {
                    Log::error('PayOS createPaymentLink failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'payment_id' => $payment->payment_id,
                        'ticket_id' => $ticket->ticket_id,
                    ]);
                    
                    $errorMessage = 'Không thể tạo link thanh toán. Vui lòng thử lại sau.';
                    if (config('app.debug')) {
                        $errorMessage .= ' Chi tiết: ' . $e->getMessage();
                    }
                    
                    return redirect()->back()->with('error', $errorMessage);
                }
            }

            return redirect()->route('tickets.index', ['new' => '1'])->with('success', 'Đơn hàng đã được tạo. Vui lòng chờ tổ chức xác nhận thanh toán tiền mặt.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to purchase tickets', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi mua vé. Vui lòng thử lại sau.');
        }
    }

    /**
     * Lấy danh sách vé của user
     */
    public function myTickets(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['ticketType.event', 'ticketType', 'payment'])
            ->where('attendee_id', $user->user_id);

        $tickets = $query->orderBy('purchase_time', 'desc')
            ->paginate(12);

        if (! $request->has('from') && ! $request->has('new')) {
            $request->session()->forget(['success', 'error', 'warning', 'info']);
        }

        return view('tickets.my', compact('tickets'));
    }

    /**
     * Lấy danh sách sự kiện đã kết thúc mà user đã mua vé (đã thanh toán)
     */
    public function endedEvents(Request $request)
    {
        $user = Auth::user();

        $endedEvents = Event::whereHas('ticketTypes.tickets', function ($q) use ($user) {
            $q->where('attendee_id', $user->user_id)
                ->where('payment_status', 'paid');
        })
            ->where('end_time', '<', now())
            ->where('approved', 1)
            ->with(['category', 'location'])
            ->with(['reviews' => function ($q) use ($user) {
                $q->where('user_id', $user->user_id);
            }])
            ->orderBy('end_time', 'desc')
            ->paginate(12);

        return view('events.ended', compact('endedEvents'));
    }

    /**
     * Xem chi tiết vé
     */
    public function show(Request $request, $ticketId)
    {
        if ($request->has('ticket') && is_object($request->ticket)) {
            $ticket = $request->ticket;
        } else {
            $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        }

        $ticket->load(['ticketType.event', 'ticketType', 'payment.paymentMethod']);

        $qrCode = $ticket->qr_code ?? $this->qrCodeService->generateQRCode($ticket);
        $qrImageUrl = $this->qrCodeService->generateQRCodeUrl($qrCode);

        return view('tickets.show', compact('ticket', 'qrCode', 'qrImageUrl'));
    }

    /**
     * Lấy danh sách ticket types của event
     */
    public function getTicketTypes($eventId)
    {
        $event = Event::where('event_id', $eventId)->firstOrFail();
        $ticketTypes = TicketType::where('event_id', $eventId)
            ->where('total_quantity', '>', 0)
            ->get();

        return view('events.ticket-types', compact('ticketTypes', 'eventId', 'event'));
    }

    /**
     * Check-in ticket tại event
     */
    public function checkIn(Request $request, $ticketId)
    {
        $ticket = Ticket::with(['ticketType.event'])->where('ticket_id', $ticketId)->firstOrFail();

        if ($ticket->payment_status !== 'paid') {
            return redirect()->back()->with('warning', 'Vé chưa thanh toán!');
        }

        $event = $ticket->ticketType->event;
        if (now()->lt($event->start_time)) {
            return redirect()->back()->with('warning', 'Sự kiện chưa bắt đầu!');
        }

        try {
            AdminLog::logUserAction(null, 'check_in_ticket', 'tickets', $ticketId, null, [
                'event_id' => $event->event_id,
                'ticket_id' => $ticketId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log check-in action', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Check-in thành công!');
    }
}
