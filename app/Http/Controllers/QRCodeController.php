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
     * Lấy QR code cho ticket
     */
    public function getTicketQR(int $ticketId)
    {
        try {
            $ticket = Ticket::where('ticket_id', $ticketId)
                ->where('attendee_id', Auth::id())
                ->with(['ticketType.event', 'ticketType'])
                ->first();

            if (! $ticket) {
                return redirect()->back()->with('error', 'Không tìm thấy vé hoặc không có quyền truy cập');
            }

            $qrCode = $this->qrCodeService->generateQRCode($ticket);
            $qrImageUrl = $this->qrCodeService->generateQRCodeUrl($qrCode);

            return view('qr.ticket', compact('qrCode', 'qrImageUrl', 'ticket'))
                ->with('success', 'Lấy QR code thành công!');

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
            $event = Event::findOrFail($eventId);
            $user = Auth::user();
            if ($event->organizer_id !== $user->user_id) {
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
     * Lấy danh sách attendees đã check-in (cho organizer/admin)
     */
    public function getCheckedInAttendees(int $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            $user = Auth::user();
            if ($event->organizer_id !== $user->user_id) {
                return redirect()->back()->with('warning', 'Không có quyền truy cập danh sách này');
            }
            $checkedInTickets = Ticket::whereHas('ticketType', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
                ->where('payment_status', 'used')
                ->with(['attendee', 'ticketType'])
                ->orderBy('purchase_time', 'desc')
                ->paginate(50);

            return view('qr.attendees', compact('event', 'checkedInTickets'))
                ->with('success', 'Lấy danh sách check-in thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy danh sách: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị trang scanner QR code cho event
     */
    public function showScanner(int $eventId)
    {
        return view('qr.scanner', compact('eventId'));
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
            $ticket = \App\Models\Ticket::where('qr_code', $request->qr_code)
                ->whereHas('ticketType', function ($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->first();
            if (! $ticket) {
                return redirect()->back()->with('error', 'QR code không hợp lệ hoặc không thuộc event này.');
            }
            if ($ticket->payment_status !== 'paid') {
                return redirect()->back()->with('warning', 'Vé chưa thanh toán hoặc đã sử dụng.');
            }
            $ticket->payment_status = 'used';
            $ticket->checked_in_at = now();
            $ticket->save();

            return redirect()->back()->with('success', 'Check-in thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi check-in: '.$e->getMessage());
        }
    }
}
