<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service quản lý QR code cho tickets
 */
class QRCodeService
{
    /**
     * Tạo QR code cho ticket
     */
    public function generateQRCode(Ticket $ticket): string
    {
        try {
            // Tạo unique QR code nếu chưa có
            if (empty($ticket->qr_code)) {
                $qrCode = $this->generateUniqueQRCode();
                $ticket->update(['qr_code' => $qrCode]);
            }

            return $ticket->qr_code;

        } catch (\Exception $e) {
            Log::error('Lỗi tạo QR code: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo unique QR code string
     */
    private function generateUniqueQRCode(): string
    {
        do {
            // Format: QR_TIMESTAMP_RANDOM
            $qrCode = 'QR_' . time() . '_' . Str::upper(Str::random(8));
        } while (Ticket::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Verify QR code và trả về ticket info
     */
    public function verifyQRCode(string $qrCode): ?array
    {
        try {
            $ticket = Ticket::where('qr_code', $qrCode)
                ->with(['event', 'ticketType', 'attendee'])
                ->first();

            if (!$ticket) {
                return null;
            }

            return [
                'valid' => true,
                'ticket_id' => $ticket->ticket_id,
                'event_name' => $ticket->event->event_name ?? $ticket->event->title,
                'attendee_name' => $ticket->attendee->full_name,
                'ticket_type' => $ticket->ticketType->name,
                'status' => $ticket->status,
                'can_check_in' => $this->canCheckIn($ticket)
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi verify QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra xem có thể check-in không
     */
    private function canCheckIn(Ticket $ticket): bool
    {
        // Ticket phải active
        if ($ticket->status !== 'active') {
            return false;
        }

        // Event phải là ongoing hoặc upcoming trong ngày
        $now = now();
        $eventStart = $ticket->event->start_time;
        $eventEnd = $ticket->event->end_time;

        // Cho phép check-in từ 2 tiếng trước đến khi event kết thúc
        $checkInStart = $eventStart->subHours(2);
        
        return $now->between($checkInStart, $eventEnd);
    }

    /**
     * Check-in ticket bằng QR code
     */
    public function checkIn(string $qrCode, ?int $staffId = null): array
    {
        try {
            $ticket = Ticket::where('qr_code', $qrCode)->first();

            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'QR code không hợp lệ'
                ];
            }

            if ($ticket->status === 'used') {
                return [
                    'success' => false,
                    'message' => 'Vé đã được sử dụng trước đó',
                    'checked_in_at' => $ticket->checked_in_at
                ];
            }

            if (!$this->canCheckIn($ticket)) {
                return [
                    'success' => false,
                    'message' => 'Không thể check-in vào thời điểm này'
                ];
            }

            // Thực hiện check-in
            $ticket->update([
                'status' => 'used',
                'checked_in_at' => now(),
                'checked_in_by' => $staffId
            ]);

            return [
                'success' => true,
                'message' => 'Check-in thành công',
                'ticket' => [
                    'attendee_name' => $ticket->attendee->full_name,
                    'event_name' => $ticket->event->event_name ?? $ticket->event->title,
                    'ticket_type' => $ticket->ticketType->name,
                    'checked_in_at' => $ticket->checked_in_at->format('d/m/Y H:i')
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi check-in QR code: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi check-in'
            ];
        }
    }

    /**
     * Tạo QR code URL để hiển thị
     */
    public function generateQRCodeUrl(string $qrCode): string
    {
        // Sử dụng API miễn phí để tạo QR code image
        $baseUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        $params = http_build_query([
            'size' => '200x200',
            'data' => $qrCode,
            'format' => 'png'
        ]);

        return $baseUrl . '?' . $params;
    }

    /**
     * Lấy thống kê check-in cho event
     */
    public function getCheckInStats(int $eventId): array
    {
        try {
            $totalTickets = Ticket::where('event_id', $eventId)->count();
            $checkedInTickets = Ticket::where('event_id', $eventId)
                                    ->where('status', 'used')
                                    ->count();

            $checkInRate = $totalTickets > 0 ? ($checkedInTickets / $totalTickets) * 100 : 0;

            return [
                'total_tickets' => $totalTickets,
                'checked_in' => $checkedInTickets,
                'not_checked_in' => $totalTickets - $checkedInTickets,
                'check_in_rate' => round($checkInRate, 2)
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi lấy thống kê check-in: ' . $e->getMessage());
            return [
                'total_tickets' => 0,
                'checked_in' => 0,
                'not_checked_in' => 0,
                'check_in_rate' => 0
            ];
        }
    }
}
