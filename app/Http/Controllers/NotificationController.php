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
    public function markAsRead(int $notificationId)
    {
        try {
            $userId = Auth::id();
            $success = $this->notificationService->markAsRead($notificationId, $userId);
            if ($success) {
                return redirect()->back()->with('success', 'Đã đánh dấu thông báo là đã đọc!');
            }
            return redirect()->back()->with('warning', 'Không tìm thấy thông báo hoặc không có quyền truy cập!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi đánh dấu thông báo: ' . $e->getMessage());
        }
    }

    /**
     * Đánh dấu tất cả notifications đã đọc
     * 
     * @return JsonResponse
     */
    public function markAllAsRead()
    {
        try {
            $userId = Auth::id();
            $success = $this->notificationService->markAllAsRead($userId);

            if ($success) {
                return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc');
            }
            return redirect()->back()->with('warning', 'Không thể cập nhật thông báo');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi cập nhật thông báo: ' . $e->getMessage());
        }
    }

    /**
     * Lấy số lượng notifications chưa đọc
     * 
     * @return JsonResponse
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            $unreadCount = $this->notificationService->getUnreadCount($userId);

            return redirect()->back()->with('success', 'Lấy số lượng thông báo chưa đọc thành công');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy số lượng thông báo chưa đọc: ' . $e->getMessage());
        }
    }
}