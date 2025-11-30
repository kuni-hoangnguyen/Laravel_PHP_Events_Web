<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý các API liên quan đến notifications
 */
class NotificationController extends WelcomeController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Lấy danh sách notifications của user hiện tại
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 20);

        $notifications = $this->notificationService->getUserNotifications($userId, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Đánh dấu một notification đã đọc
     * 
     * @param int $notificationId
     * @return JsonResponse
     */
    public function markAsRead(int $notificationId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $success = $this->notificationService->markAsRead($notificationId, $userId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã đánh dấu thông báo là đã đọc'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông báo hoặc không có quyền truy cập'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả notifications đã đọc
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $success = $this->notificationService->markAllAsRead($userId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã đánh dấu tất cả thông báo là đã đọc'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật thông báo'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy số lượng notifications chưa đọc
     * 
     * @return JsonResponse
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $unreadCount = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy số lượng thông báo chưa đọc: ' . $e->getMessage()
            ], 500);
        }
    }
}