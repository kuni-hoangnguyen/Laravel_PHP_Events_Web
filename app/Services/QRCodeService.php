<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

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
            // Sử dụng helper của model
            return $ticket->generateQrCode();
        } catch (\Exception $e) {
            Log::error('Lỗi tạo QR code: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify QR code và trả về ticket info
     */
    public function verifyQRCode(string $qrCode): ?array
    {
        try {
            $ticket = Ticket::where('qr_code', $qrCode)
                ->with(['ticketType.event', 'attendee'])
                ->first();
            if (! $ticket) {
                return null;
            }

            return [
                'valid' => true,
                'ticket_id' => $ticket->ticket_id,
                'event_name' => $ticket->getEventAttribute()->event_name ?? $ticket->getEventAttribute()->title,
                'attendee_name' => $ticket->attendee->full_name,
                'ticket_type' => $ticket->ticketType->name,
                'payment_status' => $ticket->payment_status,
                'can_check_in' => $this->canCheckIn($ticket),
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi verify QR code: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Kiểm tra xem có thể check-in không
     */
    private function canCheckIn(Ticket $ticket): bool
    {
        // Vé phải paid
        if (! $ticket->isPaid()) {
            return false;
        }
        $event = $ticket->getEventAttribute();
        if (! $event) {
            return false;
        }
        $now = now();
        $checkInStart = $event->start_time->subHours(2);

        return $now->between($checkInStart, $event->end_time);
    }

    /**
     * Tạo QR code URL để hiển thị
     */
    public function generateQRCodeUrl(string $qrCode): string
    {
        $baseUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        $params = http_build_query([
            'size' => '200x200',
            'data' => $qrCode,
            'format' => 'png',
        ]);

        return $baseUrl.'?'.$params;
    }

    /**
     * Lấy thống kê check-in cho event
     */
    public function getCheckInStats(int $eventId): array
    {
        try {
            $totalTickets = Ticket::whereHas('ticketType', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })->count();
            $checkedInTickets = Ticket::whereHas('ticketType', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })->used()->count();
            $checkInRate = $totalTickets > 0 ? ($checkedInTickets / $totalTickets) * 100 : 0;

            return [
                'total_tickets' => $totalTickets,
                'checked_in' => $checkedInTickets,
                'not_checked_in' => $totalTickets - $checkedInTickets,
                'check_in_rate' => round($checkInRate, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi lấy thống kê check-in: '.$e->getMessage());

            return [
                'total_tickets' => 0,
                'checked_in' => 0,
                'not_checked_in' => 0,
                'check_in_rate' => 0,
            ];
        }
    }
}
