<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\NotificationService;
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

    public function __construct(NotificationService $notificationService, QRCodeService $qrCodeService)
    {
        $this->notificationService = $notificationService;
        $this->qrCodeService = $qrCodeService;
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

        // Kiểm tra phương thức thanh toán
        $paymentMethod = \App\Models\PaymentMethod::where('method_id', $request->payment_method_id)->firstOrFail();
        $isCashPayment = stripos($paymentMethod->name, 'tiền mặt') !== false || stripos($paymentMethod->name, 'cash') !== false;

        try {
            DB::beginTransaction();

            // Lock ticketType với pessimistic locking để tránh lost update
            $ticketType = TicketType::where('ticket_type_id', $request->ticket_type_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Kiểm tra lại số lượng sau khi lock (double-check)
            if ($ticketType->remaining_quantity < $request->quantity) {
                DB::rollBack();

                return redirect()->back()->with('warning', 'Số lượng vé không đủ! Vui lòng thử lại.');
            }

            // Atomic update: Giảm số lượng remaining với điều kiện WHERE để đảm bảo số lượng đủ
            $updated = DB::table('ticket_types')
                ->where('ticket_type_id', $request->ticket_type_id)
                ->where('remaining_quantity', '>=', $request->quantity)
                ->decrement('remaining_quantity', $request->quantity);

            // Nếu update không thành công (số lượng không đủ), rollback
            if ($updated === 0) {
                DB::rollBack();

                return redirect()->back()->with('warning', 'Số lượng vé không đủ! Có thể đã có người khác mua trước đó.');
            }

            // Refresh ticketType để lấy giá trị mới
            $ticketType->refresh();

            // Tạo 1 ticket với quantity
            $totalAmount = $ticketType->price * $request->quantity;
            
            $ticket = Ticket::create([
                'ticket_type_id' => $request->ticket_type_id,
                'attendee_id' => Auth::id(),
                'quantity' => $request->quantity,
                'payment_status' => 'pending', // Giữ pending, chờ tổ chức xác nhận
                'purchase_time' => now(),
            ]);

            // Tạo QR code cho ticket (1 QR code cho toàn bộ số lượng vé)
            $this->qrCodeService->generateQRCode($ticket);

            // Tạo payment cho ticket (tổng tiền = price * quantity)
            if ($isCashPayment) {
                // Thanh toán tiền mặt: status = 'failed' (chưa xác nhận), chờ organizer xác nhận
                $payment = Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'amount' => $totalAmount, // Tổng tiền = price * quantity
                    'method_id' => $request->payment_method_id,
                    'status' => 'failed', // Chưa xác nhận, sẽ update thành 'success' khi organizer xác nhận
                    'transaction_id' => 'CASH_'.time().'_'.Auth::id(),
                    'paid_at' => null, // Chưa thanh toán
                ]);
                // Ticket vẫn giữ payment_status = 'pending'
            } else {
                // Các phương thức khác (sẽ thêm sau)
                $payment = Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'amount' => $totalAmount, // Tổng tiền = price * quantity
                    'method_id' => $request->payment_method_id,
                    'status' => 'failed',
                    'transaction_id' => 'TXN_'.time().'_'.Auth::id(),
                    'paid_at' => null,
                ]);
            }

            // Refresh ticket để đảm bảo lấy dữ liệu mới nhất từ DB (tránh cache)
            $ticket->refresh();
            $ticket->load('payment');

            // Gửi notification cho organizer về thanh toán tiền mặt chờ xác nhận (chỉ khi thanh toán tiền mặt)
            $event = Event::where('event_id', $eventId)->first();
            if ($isCashPayment) {
                // Gửi thông báo cho organizer về thanh toán tiền mặt chờ xác nhận
                $this->notificationService->notifyCashPaymentPending(
                    $event->organizer_id,
                    Auth::user()->full_name,
                    $event->event_name ?? $event->title,
                    $eventId,
                    $request->quantity,
                    $totalAmount
                );
            }
            // Không gửi thông báo cho người mua vé ngay, chỉ gửi khi thanh toán được xác nhận thành công

            DB::commit();

            // Log action
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

            if ($isCashPayment) {
                return redirect()->route('tickets.index', ['new' => '1'])->with('success', 'Đơn hàng đã được tạo. Vui lòng chờ tổ chức xác nhận thanh toán tiền mặt.');
            } else {
                return redirect()->route('tickets.index', ['new' => '1'])->with('success', 'Mua vé thành công!');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Lỗi khi mua vé: '.$e->getMessage());
        }
    }

    /**
     * Lấy danh sách vé của user
     */
    public function myTickets(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['ticketType.event', 'ticketType', 'payment']);

        // Admin có thể xem tất cả vé, user thường chỉ xem vé của mình
        if (! $user->isAdmin()) {
            $query->where('attendee_id', $user->user_id);
        }

        $tickets = $query->orderBy('purchase_time', 'desc')
            ->paginate(12);

        // Chỉ giữ flash message nếu có tham số từ redirect mới
        // Nếu không có, xóa flash message để tránh hiển thị lại khi quay lại từ trang khác
        if (! $request->has('from') && ! $request->has('new')) {
            $request->session()->forget(['success', 'error', 'warning', 'info']);
        }

        return view('tickets.my', compact('tickets'));
    }

    /**
     * Lấy danh sách sự kiện đã kết thúc mà user đã mua vé
     */
    public function endedEvents(Request $request)
    {
        $user = Auth::user();

        // Lấy các event đã kết thúc mà user đã mua vé (đã thanh toán)
        // Sử dụng whereHas với nested relationship
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
        // Lấy ticket từ middleware hoặc load trực tiếp
        if ($request->has('ticket') && is_object($request->ticket)) {
            $ticket = $request->ticket;
        } else {
            // Fallback: Load trực tiếp (trường hợp middleware không set)
            $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();
        }

        $ticket->load(['ticketType.event', 'ticketType', 'payment.paymentMethod']);

        // Tạo QR code nếu chưa có và lấy URL
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

        // Kiểm tra thời gian event
        $event = $ticket->ticketType->event;
        if (now()->lt($event->start_time)) {
            return redirect()->back()->with('warning', 'Sự kiện chưa bắt đầu!');
        }

        // Note: check_in_time field doesn't exist in tickets table
        // This functionality would need a separate check_ins table or field addition
        // For now, we'll just return success

        // Log action
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
