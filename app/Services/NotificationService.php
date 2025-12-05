<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service xử lý hệ thống thông báo
 * Tự động gửi notifications cho các events quan trọng
 */
class NotificationService
{
    /**
     * Tạo thông báo mới cho user
     */
    public function createNotification(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): ?Notification {
        try {
            return Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
                'action_url' => $actionUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo notification: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Thông báo cho admin về sự kiện mới cần duyệt
     */
    public function notifyAdminNewEvent(int $eventId, string $eventName, string $organizerName): void
    {
        try {
            $admins = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'admin');
            })->get();

            $actionUrl = route('admin.events.index', ['status' => 'pending']);

            foreach ($admins as $admin) {
                $this->createNotification(
                    $admin->user_id,
                    'Sự kiện mới cần duyệt',
                    "Organizer '{$organizerName}' đã tạo sự kiện mới '{$eventName}' và đang chờ phê duyệt.",
                    'info',
                    $actionUrl
                );
            }

            Log::info('Notifications sent to admins about new event', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about new event', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Thông báo cho admin về yêu cầu hủy sự kiện
     */
    public function notifyAdminCancellationRequest(int $eventId, string $eventName, string $organizerName, string $reason): void
    {
        try {
            Log::info('Starting to notify admins about cancellation request', [
                'event_id' => $eventId,
                'event_name' => $eventName,
            ]);

            $admins = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'admin');
            })->get();

            Log::info('Found admins for cancellation notification', [
                'admin_count' => $admins->count(),
                'admin_ids' => $admins->pluck('user_id')->toArray(),
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admins found to notify about cancellation request', [
                    'event_id' => $eventId,
                ]);

                return;
            }

            $actionUrl = route('admin.events.index', ['status' => 'cancellation']);

            $notifiedCount = 0;
            foreach ($admins as $admin) {
                try {
                    $notification = $this->createNotification(
                        $admin->user_id,
                        'Yêu cầu hủy sự kiện',
                        "Organizer '{$organizerName}' đã yêu cầu hủy sự kiện '{$eventName}'. Lý do: {$reason}",
                        'warning',
                        $actionUrl
                    );

                    if ($notification) {
                        $notifiedCount++;
                        Log::info('Notification created for admin', [
                            'admin_id' => $admin->user_id,
                            'notification_id' => $notification->notification_id,
                        ]);
                    } else {
                        Log::warning('Failed to create notification for admin', [
                            'admin_id' => $admin->user_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error creating notification for admin', [
                        'admin_id' => $admin->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Notifications sent to admins about cancellation request', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'admin_count' => $admins->count(),
                'notified_count' => $notifiedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about cancellation request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Thông báo khi yêu cầu hủy được duyệt
     */
    public function notifyCancellationApproved(int $organizerId, string $eventName): void
    {
        $this->createNotification(
            $organizerId,
            'Yêu cầu hủy sự kiện đã được duyệt',
            "Yêu cầu hủy sự kiện '{$eventName}' đã được admin chấp thuận. Sự kiện đã được đánh dấu là đã hủy.",
            'info'
        );
    }

    /**
     * Thông báo cho tất cả attendees khi sự kiện bị hủy
     */
    public function notifyAttendeesEventCancelled(int $eventId, string $eventName): void
    {
        try {
            $tickets = Ticket::whereHas('ticketType', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
                ->where('payment_status', 'paid')
                ->with('attendee')
                ->get();

            $actionUrl = route('tickets.index');

            foreach ($tickets as $ticket) {
                if ($ticket->attendee) {
                    $this->createNotification(
                        $ticket->attendee_id,
                        'Sự kiện đã bị hủy',
                        "Sự kiện '{$eventName}' mà bạn đã mua vé đã bị hủy. Vui lòng kiểm tra thông tin hoàn tiền nếu có.",
                        'warning',
                        $actionUrl
                    );
                }
            }

            Log::info('Notifications sent to attendees about event cancellation', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'attendee_count' => $tickets->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify attendees about event cancellation', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Thông báo khi yêu cầu hủy bị từ chối
     */
    public function notifyCancellationRejected(int $organizerId, string $eventName): void
    {
        $this->createNotification(
            $organizerId,
            'Yêu cầu hủy sự kiện bị từ chối',
            "Yêu cầu hủy sự kiện '{$eventName}' đã bị admin từ chối. Sự kiện vẫn tiếp tục diễn ra.",
            'warning'
        );
    }

    /**
     * Thông báo khi event được approve
     */
    public function notifyEventApproved(int $organizerId, string $eventName): void
    {
        $this->createNotification(
            $organizerId,
            'Sự kiện được duyệt',
            "Sự kiện '{$eventName}' đã được admin phê duyệt và có thể bán vé.",
            'success'
        );
    }

    /**
     * Thông báo khi event bị reject
     */
    public function notifyEventRejected(int $organizerId, string $eventName, string $reason = ''): void
    {
        $message = "Sự kiện '{$eventName}' đã bị từ chối.";
        if ($reason) {
            $message .= " Lý do: {$reason}";
        }

        $this->createNotification(
            $organizerId,
            'Sự kiện bị từ chối',
            $message,
            'warning'
        );
    }

    /**
     * Thông báo khi mua vé thành công
     */
    public function notifyTicketPurchased(int $userId, string $eventName, int $quantity, float $amount): void
    {
        $this->createNotification(
            $userId,
            'Mua vé thành công',
            "Bạn đã mua thành công {$quantity} vé cho sự kiện '{$eventName}'. Tổng tiền: ".number_format($amount, 0, ',', '.').' VND',
            'success'
        );
    }

    /**
     * Thông báo cho organizer về thanh toán tiền mặt chờ xác nhận
     */
    public function notifyCashPaymentPending(int $organizerId, string $attendeeName, string $eventName, int $eventId, int $quantity, float $amount): void
    {
        $actionUrl = route('events.pending-payments', $eventId);
        $this->createNotification(
            $organizerId,
            'Thanh toán tiền mặt chờ xác nhận',
            "{$attendeeName} đã mua {$quantity} vé cho sự kiện '{$eventName}' bằng tiền mặt. Tổng tiền: ".number_format($amount, 0, ',', '.').' VND. Vui lòng xác nhận khi nhận được tiền.',
            'info',
            $actionUrl
        );
    }

    /**
     * Thông báo nhắc nhở event sắp diễn ra
     */
    public function notifyEventReminder(array $userIds, string $eventName, string $startTime): void
    {
        foreach ($userIds as $userId) {
            $this->createNotification(
                $userId,
                'Sự kiện sắp diễn ra',
                "Sự kiện '{$eventName}' sẽ bắt đầu vào {$startTime}. Đừng quên tham gia!",
                'info'
            );
        }
    }

    /**
     * Thông báo cho admin về yêu cầu hoàn tiền mới
     */
    public function notifyAdminRefundRequest(int $refundId, string $eventName, string $requesterName, float $amount, string $reason): void
    {
        try {
            $admins = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'admin');
            })->get();

            $actionUrl = route('admin.refunds.index', ['status' => 'pending']);

            foreach ($admins as $admin) {
                $this->createNotification(
                    $admin->user_id,
                    'Yêu cầu hoàn tiền mới',
                    "Người dùng '{$requesterName}' đã yêu cầu hoàn tiền cho sự kiện '{$eventName}'. Số tiền: ".number_format($amount, 0, ',', '.').' VND. Lý do: '.Str::limit($reason, 100),
                    'warning',
                    $actionUrl
                );
            }

            Log::info('Notifications sent to admins about refund request', [
                'refund_id' => $refundId,
                'event_name' => $eventName,
                'admin_count' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about refund request', [
                'error' => $e->getMessage(),
                'refund_id' => $refundId,
            ]);
        }
    }

    /**
     * Thông báo cho organizer về yêu cầu hoàn tiền cho sự kiện của họ
     */
    public function notifyOrganizerRefundRequest(int $organizerId, string $eventName, string $requesterName, float $amount, string $reason, int $eventId): void
    {
        try {
            $actionUrl = route('events.show', $eventId);

            $this->createNotification(
                $organizerId,
                'Yêu cầu hoàn tiền cho sự kiện của bạn',
                "Người dùng '{$requesterName}' đã yêu cầu hoàn tiền cho sự kiện '{$eventName}'. Số tiền: ".number_format($amount, 0, ',', '.').' VND. Lý do: '.Str::limit($reason, 100),
                'warning',
                $actionUrl
            );

            Log::info('Notification sent to organizer about refund request', [
                'organizer_id' => $organizerId,
                'event_id' => $eventId,
                'event_name' => $eventName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify organizer about refund request', [
                'error' => $e->getMessage(),
                'organizer_id' => $organizerId,
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Thông báo về trạng thái refund cho user
     */
    public function notifyRefundStatus(int $userId, string $eventName, string $status, ?float $amount = null, ?string $actionUrl = null): void
    {
        $messages = [
            'pending' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đang được xử lý.",
            'approved' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đã được chấp nhận. Số tiền ".number_format($amount ?? 0, 0, ',', '.').' VND sẽ được hoàn lại.',
            'rejected' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đã bị từ chối.",
        ];

        $types = [
            'pending' => 'info',
            'approved' => 'success',
            'rejected' => 'warning',
        ];

        $this->createNotification(
            $userId,
            'Cập nhật hoàn tiền',
            $messages[$status] ?? 'Trạng thái hoàn tiền đã được cập nhật.',
            $types[$status] ?? 'info',
            $actionUrl
        );
    }

    /**
     * Thông báo khi thanh toán được xác nhận thành công (cho người mua vé)
     */
    public function notifyPaymentConfirmed(int $userId, string $eventName, int $ticketId, float $amount): void
    {
        try {
            $actionUrl = route('tickets.show', $ticketId);
            $notification = $this->createNotification(
                $userId,
                'Mua vé thành công',
                "Thanh toán cho sự kiện '{$eventName}' đã được xác nhận thành công. Số tiền: ".number_format($amount, 0, ',', '.').' VND. Vé của bạn đã sẵn sàng!',
                'success',
                $actionUrl
            );

            if ($notification) {
                Log::info('Payment confirmation notification created', [
                    'notification_id' => $notification->notification_id,
                    'user_id' => $userId,
                    'action_url' => $actionUrl,
                ]);
            } else {
                Log::warning('Failed to create payment confirmation notification', [
                    'user_id' => $userId,
                    'ticket_id' => $ticketId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in notifyPaymentConfirmed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'ticket_id' => $ticketId,
            ]);
            throw $e;
        }
    }

    /**
     * Thông báo khi thanh toán bị từ chối
     */
    public function notifyPaymentRejected(int $userId, string $eventName, int $ticketId): void
    {
        try {
            $actionUrl = route('tickets.show', $ticketId);
            $notification = $this->createNotification(
                $userId,
                'Thanh toán bị từ chối',
                "Thanh toán tiền mặt cho sự kiện '{$eventName}' đã bị từ chối. Vé đã được hủy.",
                'error',
                $actionUrl
            );

            if ($notification) {
                Log::info('Payment rejection notification created', [
                    'notification_id' => $notification->notification_id,
                    'user_id' => $userId,
                    'action_url' => $actionUrl,
                ]);
            } else {
                Log::warning('Failed to create payment rejection notification', [
                    'user_id' => $userId,
                    'ticket_id' => $ticketId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in notifyPaymentRejected', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'ticket_id' => $ticketId,
            ]);
        }
    }

    /**
     * Thông báo cho organizer khi xác nhận thanh toán tiền mặt thành công
     */
    public function notifyOrganizerPaymentConfirmed(int $organizerId, string $attendeeName, string $eventName, int $paymentId, float $amount): void
    {
        try {
            $payment = \App\Models\Payment::with('ticket')->find($paymentId);
            $actionUrl = $payment && $payment->ticket ? route('tickets.show', $payment->ticket->ticket_id) : route('payments.index');
            $notification = $this->createNotification(
                $organizerId,
                'Đã xác nhận thanh toán tiền mặt',
                "Bạn đã xác nhận thanh toán tiền mặt của {$attendeeName} cho sự kiện '{$eventName}'. Số tiền: ".number_format($amount, 0, ',', '.').' VND.',
                'success',
                $actionUrl
            );

            if ($notification) {
                Log::info('Organizer payment confirmation notification created', [
                    'notification_id' => $notification->notification_id,
                    'organizer_id' => $organizerId,
                    'action_url' => $actionUrl,
                ]);
            } else {
                Log::warning('Failed to create organizer payment confirmation notification', [
                    'organizer_id' => $organizerId,
                    'payment_id' => $paymentId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in notifyOrganizerPaymentConfirmed', [
                'error' => $e->getMessage(),
                'organizer_id' => $organizerId,
                'payment_id' => $paymentId,
            ]);
            throw $e;
        }
    }

    /**
     * Lấy danh sách notifications của user
     */
    public function getUserNotifications(int $userId, int $limit = 20)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('notification_id', 'desc')
            ->paginate($limit);
    }

    /**
     * Đánh dấu notification đã đọc
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = Notification::where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Lỗi mark notification as read: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Đánh dấu tất cả notifications của user đã đọc
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi mark all notifications as read: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Đếm số notifications chưa đọc
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Xóa notifications cũ (cleanup)
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        try {
            $deletedCount = Notification::where('created_at', '<', now()->subDays($days))
                ->delete();

            Log::info("Đã xóa {$deletedCount} notifications cũ hơn {$days} ngày");

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Lỗi cleanup notifications: '.$e->getMessage());

            return 0;
        }
    }
}
