<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý QR code cho tickets
 */
class QRCodeController extends WelcomeController
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Lấy QR code cho ticket (trả về view)
     */
    public function getTicketQR(int $ticketId)
    {
        try {
            $user = Auth::user();
            $query = Ticket::where('ticket_id', $ticketId)
                ->with(['ticketType.event', 'ticketType', 'attendee']);

            if (! $user->isAdmin()) {
                $query->where('attendee_id', $user->user_id);
            }

            $ticket = $query->first();

            if (! $ticket) {
                return redirect()->back()->with('error', 'Không tìm thấy vé hoặc không có quyền truy cập');
            }

            $qrCode = $ticket->qr_code ?? $this->qrCodeService->generateQRCode($ticket);
            $qrImageUrl = $this->qrCodeService->generateQRCodeUrl($qrCode);

            return view('qr.ticket', compact('qrCode', 'qrImageUrl', 'ticket'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi tạo QR code: '.$e->getMessage());
        }
    }

    /**
     * Lấy thống kê check-in cho event (chỉ organizer/admin)
     */
    public function getCheckInStats(int $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();
            $user = Auth::user();

            if (! $user->isAdmin() && $event->organizer_id !== $user->user_id) {
                return redirect()->back()->with('warning', 'Không có quyền truy cập thống kê này');
            }
            $stats = $this->qrCodeService->getCheckInStats($eventId);

            return view('qr.stats', compact('event', 'stats'))
                ->with('success', 'Lấy thống kê check-in thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy thống kê: '.$e->getMessage());
        }
    }

    /**
     * Lấy danh sách attendees đã mua vé (cho organizer/admin)
     */
    public function getCheckedInAttendees(int $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();
            $user = Auth::user();

            if (! $user->isAdmin() && $event->organizer_id !== $user->user_id) {
                return redirect()->back()->with('warning', 'Không có quyền truy cập danh sách này');
            }
            
            $tickets = Ticket::whereHas('ticketType', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
                ->whereIn('payment_status', ['paid', 'used'])
                ->with(['attendee', 'ticketType', 'payment'])
                ->orderBy('purchase_time', 'desc')
                ->paginate(50);

            $totalRevenue = \App\Models\Payment::whereHas('ticket.ticketType', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->where('status', 'success')
            ->sum('amount');

            return view('qr.attendees', compact('event', 'tickets', 'totalRevenue'))
                ->with('success', 'Lấy danh sách người mua vé thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy danh sách: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị trang scanner QR code cho event
     */
    public function showScanner(int $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();
            $user = Auth::user();

            if ($event->organizer_id !== $user->user_id && ! $user->isAdmin()) {
                return redirect()->back()->with('warning', 'Không có quyền truy cập QR scanner cho sự kiện này');
            }

            return view('qr.scanner', compact('event', 'eventId'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không tìm thấy sự kiện: '.$e->getMessage());
        }
    }

    /**
     * Check-in QR code theo event (dùng cho scanner view)
     */
    public function checkInByEvent(Request $request, $eventId)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();
            $user = Auth::user();
            if ($event->organizer_id !== $user->user_id && ! $user->isAdmin()) {
                return redirect()->back()->with('warning', 'Không có quyền check-in cho sự kiện này');
            }

            $ticket = Ticket::where('qr_code', $request->qr_code)
                ->whereHas('ticketType', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->with(['ticketType.event', 'attendee'])
                ->first();

            if (! $ticket) {
                return redirect()->back()->with('error', 'QR code không hợp lệ hoặc không thuộc sự kiện này.');
            }

            if ($ticket->payment_status !== 'paid') {
                return redirect()->back()->with('warning', 'Vé chưa thanh toán. Trạng thái hiện tại: '.($ticket->payment_status == 'pending' ? 'Chờ thanh toán' : ($ticket->payment_status == 'used' ? 'Đã sử dụng' : 'Đã hủy')));
            }

            if ($ticket->checked_in_at) {
                return redirect()->back()->with('info', 'Vé đã được check-in vào lúc: '.$ticket->checked_in_at->format('d/m/Y H:i'));
            }

            $event = $ticket->ticketType->event;
            $checkInStartTime = $event->start_time->copy()->subHours(2);
            if (now()->lt($checkInStartTime)) {
                return redirect()->back()->with('warning', 'Chưa đến thời gian check-in. Có thể check-in từ: '.$checkInStartTime->format('d/m/Y H:i'));
            }

            $ticket->payment_status = 'used';
            $ticket->checked_in_at = now();
            $ticket->save();

            $quantity = $ticket->quantity ?? 1;
            $message = 'Check-in thành công! Người tham gia: '.($ticket->attendee->full_name ?? 'N/A');
            if ($quantity > 1) {
                $message .= ' (Số lượng: '.$quantity.' vé)';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi check-in: '.$e->getMessage());
        }
    }
}
