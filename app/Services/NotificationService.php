<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
        string $type = 'info'
    ): ?Notification {
        try {
            return Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo notification: ' . $e->getMessage());
            return null;
        }
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
            "Bạn đã mua thành công {$quantity} vé cho sự kiện '{$eventName}'. Tổng tiền: " . number_format($amount, 0, ',', '.') . " VND",
            'success'
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
     * Thông báo về trạng thái refund
     */
    public function notifyRefundStatus(int $userId, string $eventName, string $status, ?float $amount = null): void
    {
        $messages = [
            'pending' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đang được xử lý.",
            'approved' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đã được chấp nhận. Số tiền " . number_format($amount ?? 0, 0, ',', '.') . " VND sẽ được hoàn lại.",
            'rejected' => "Yêu cầu hoàn tiền cho sự kiện '{$eventName}' đã bị từ chối."
        ];

        $types = [
            'pending' => 'info',
            'approved' => 'success', 
            'rejected' => 'warning'
        ];

        $this->createNotification(
            $userId,
            'Cập nhật hoàn tiền',
            $messages[$status] ?? "Trạng thái hoàn tiền đã được cập nhật.",
            $types[$status] ?? 'info'
        );
    }

    /**
     * Lấy danh sách notifications của user
     */
    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
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
            Log::error('Lỗi mark notification as read: ' . $e->getMessage());
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
            Log::error('Lỗi mark all notifications as read: ' . $e->getMessage());
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
            Log::error('Lỗi cleanup notifications: ' . $e->getMessage());
            return 0;
        }
    }
}
