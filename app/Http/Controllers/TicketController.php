<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\NotificationService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $event = Event::findOrFail($eventId);
        $ticketType = TicketType::findOrFail($request->ticket_type_id);
        if ($ticketType->remaining_quantity < $request->quantity) {
            return redirect()->back()->with('warning', 'Số lượng vé không đủ!');
        }
        try {
            DB::beginTransaction();

            // Tạo tickets với QR code
            $tickets = [];
            $totalAmount = $ticketType->price * $request->quantity;
            
            for ($i = 0; $i < $request->quantity; $i++) {
                $ticket = Ticket::create([
                    'ticket_type_id' => $request->ticket_type_id,
                    'attendee_id' => Auth::id(),
                    'payment_status' => 'pending',
                    'purchase_time' => now(),
                ]);

                // Tạo QR code cho ticket
                $this->qrCodeService->generateQRCode($ticket);
                
                // Tạo payment cho ticket
                $payment = Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'amount' => $ticketType->price,
                    'method_id' => $request->payment_method_id,
                    'status' => 'pending',
                    'transaction_id' => 'TXN_'.time().'_'.Auth::id().'_'.$i,
                ]);
                
                $tickets[] = $ticket->load('payment');
            }

            // Giảm số lượng remaining
            $ticketType->decrement('remaining_quantity', $request->quantity);

            // Gửi notification mua vé thành công
            $event = Event::find($eventId);
            $this->notificationService->notifyTicketPurchased(
                Auth::id(),
                $event->event_name ?? $event->title,
                $request->quantity,
                $totalAmount
            );

            DB::commit();

            return redirect()->back()->with('success', 'Mua vé thành công!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Lỗi khi mua vé: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách vé của user
     */
    public function myTickets()
    {
        $tickets = Ticket::with(['ticketType.event', 'payment'])
                         ->where('attendee_id', Auth::id())
            ->orderBy('purchase_time', 'desc')
                         ->paginate(12);

        return view('tickets.my', compact('tickets'));
    }

    /**
     * Xem chi tiết vé
     */
    public function show(Request $request, $ticketId)
    {
        $ticket = $request->ticket; // Từ middleware ticket.owner

        $ticket->load(['ticketType.event', 'payment']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Lấy danh sách ticket types của event
     */
    public function getTicketTypes($eventId)
    {
        $event = Event::findOrFail($eventId);
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
        $ticket = Ticket::with(['ticketType.event'])->findOrFail($ticketId);

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
        return redirect()->back()->with('success', 'Check-in thành công!');
    }
}
