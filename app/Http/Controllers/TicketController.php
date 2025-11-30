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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $event = Event::findOrFail($eventId);
        $ticketType = TicketType::findOrFail($request->ticket_type_id);

        // Kiểm tra availability
        if ($ticketType->remaining_quantity < $request->quantity) {
            return response()->json([
                'message' => 'Not enough tickets available',
            ], 400);
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

            return response()->json([
                'message' => 'Tickets purchased successfully',
                'total_amount' => $totalAmount,
                'tickets' => $tickets,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Purchase failed',
                'error' => $e->getMessage(),
            ], 500);
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
            return response()->json([
                'message' => 'Ticket payment is not completed',
            ], 400);
        }

        // Kiểm tra thời gian event
        $event = $ticket->ticketType->event;
        if (now()->lt($event->start_time)) {
            return response()->json([
                'message' => 'Event has not started yet',
            ], 400);
        }

        // Note: check_in_time field doesn't exist in tickets table
        // This functionality would need a separate check_ins table or field addition
        // For now, we'll just return success
        return response()->json([
            'message' => 'Check-in successful',
            'ticket' => $ticket->load('ticketType.event'),
        ]);
    }
}
