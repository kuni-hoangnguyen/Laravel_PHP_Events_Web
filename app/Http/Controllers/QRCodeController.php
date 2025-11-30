<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
    public function getTicketQR(int $ticketId): JsonResponse
    {
        try {
            $ticket = Ticket::where('ticket_id', $ticketId)
                          ->where('attendee_id', Auth::id())
                          ->with(['ticketType.event', 'ticketType'])
                          ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vé hoặc không có quyền truy cập'
                ], 404);
            }

            $qrCode = $this->qrCodeService->generateQRCode($ticket);
            $qrImageUrl = $this->qrCodeService->generateQRCodeUrl($qrCode);

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code' => $qrCode,
                    'qr_image_url' => $qrImageUrl,
                    'ticket' => [
                        'id' => $ticket->ticket_id,
                        'event_name' => $ticket->ticketType->event->title ?? 'N/A',
                        'ticket_type' => $ticket->ticketType->name,
                        'payment_status' => $ticket->payment_status,
                        'attendee_name' => $ticket->attendee->full_name
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify QR code (cho staff check-in)
     */
    public function verifyQR(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        try {
            $result = $this->qrCodeService->verifyQRCode($request->qr_code);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code không hợp lệ hoặc không tồn tại'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi verify QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-in bằng QR code
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        try {
            $staffId = Auth::id(); // ID của staff thực hiện check-in
            $result = $this->qrCodeService->checkIn($request->qr_code, $staffId);

            $statusCode = $result['success'] ? 200 : 400;

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê check-in cho event (chỉ organizer/admin)
     */
    public function getCheckInStats(int $eventId): JsonResponse
    {
        try {
            // Kiểm tra quyền truy cập event
            $event = Event::findOrFail($eventId);
            $user = Auth::user();

            // Chỉ organizer của event hoặc admin mới xem được
            if ($event->organizer_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập thống kê này'
                ], 403);
            }

            $stats = $this->qrCodeService->getCheckInStats($eventId);

            return response()->json([
                'success' => true,
                'data' => [
                    'event_name' => $event->event_name ?? $event->title,
                    'stats' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách attendees đã check-in (cho organizer/admin)
     */
    public function getCheckedInAttendees(int $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);
            $user = Auth::user();

            // Kiểm tra quyền truy cập
            if ($event->organizer_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập danh sách này'
                ], 403);
            }

            // Get tickets for this event through ticket types
            $checkedInTickets = Ticket::whereHas('ticketType', function($query) use ($eventId) {
                                    $query->where('event_id', $eventId);
                                })
                                ->where('payment_status', 'paid')
                                ->with(['attendee', 'ticketType'])
                                ->orderBy('purchase_time', 'desc')
                                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $checkedInTickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách: ' . $e->getMessage()
            ], 500);
        }
    }
}