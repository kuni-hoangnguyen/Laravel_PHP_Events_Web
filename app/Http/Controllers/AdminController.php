<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends WelcomeController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Dashboard admin - thống kê tổng quan
     */
    public function dashboard()
    {
        // Vé đã bán gần đây
        $recentTickets = Ticket::where('payment_status', 'paid')
            ->with(['ticketType.event', 'attendee'])
            ->orderBy('purchase_time', 'desc')
            ->take(5)
            ->get();

        // Sự kiện đã tạo gần đây
        $recentEvents = Event::with(['category', 'location', 'organizer'])
            ->latest()
            ->take(5)
            ->get();

        // Doanh thu theo từng sự kiện
        $eventsRevenue = Event::with(['organizer'])
            ->select('events.*')
            ->leftJoin('ticket_types', 'events.event_id', '=', 'ticket_types.event_id')
            ->leftJoin('tickets', 'ticket_types.ticket_type_id', '=', 'tickets.ticket_type_id')
            ->leftJoin('payments', function($join) {
                $join->on('tickets.ticket_id', '=', 'payments.ticket_id')
                     ->where('payments.status', '=', 'success');
            })
            ->selectRaw('events.*, COALESCE(SUM(payments.amount), 0) as payments_sum_amount')
            ->groupBy('events.event_id')
            ->havingRaw('COALESCE(SUM(payments.amount), 0) > 0')
            ->orderByDesc('payments_sum_amount')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('recentTickets', 'recentEvents', 'eventsRevenue'));
    }

    /**
     * Quản lý events - approve/reject
     */
    public function events(Request $request)
    {
        $query = Event::with(['organizer', 'category', 'location']);

        if ($request->filled('status') && $request->status != '') {
            if ($request->status == 'pending') {
                $query->where('approved', 0);
            } elseif ($request->status == 'approved') {
                $query->where('approved', 1);
            } elseif ($request->status == 'rejected') {
                $query->where('approved', -1);
            } elseif ($request->status == 'cancellation') {
                $query->where('cancellation_requested', true)->where('status', '!=', 'cancelled');
            }
        }

        $events = $query->latest()->paginate(15);

        // Tính doanh thu cho mỗi event
        foreach ($events as $event) {
            $event->revenue = \App\Models\Payment::whereHas('ticket.ticketType', function($q) use ($event) {
                $q->where('event_id', $event->event_id);
            })
            ->where('status', 'success')
            ->sum('amount');
        }

        return view('admin.events', compact('events'));
    }

    /**
     * Approve event
     */
    public function approveEvent(Request $request, $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();

            $event->update(['approved' => 1, 'approved_at' => now(), 'approved_by' => Auth::id()]);

            // Gửi notification cho organizer
            $this->notificationService->notifyEventApproved(
                $event->organizer_id,
                $event->event_name ?? $event->title
            );

            AdminLog::logAction(
                Auth::id(),
                'approve_event',
                'events',
                $eventId,
                ['approved' => $event->approved],
                ['approved' => 1]
            );

            return redirect()->back()->with('success', 'Duyệt sự kiện thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi duyệt sự kiện: '.$e->getMessage());
        }
    }

    /**
     * Reject event
     */
    public function rejectEvent(Request $request, $eventId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $event = Event::where('event_id', $eventId)->firstOrFail();

            $event->update([
                'approved' => -1,
            ]);

            // Gửi notification cho organizer
            $this->notificationService->notifyEventRejected(
                $event->organizer_id,
                $event->event_name ?? $event->title,
                $request->reason
            );

            AdminLog::logAction(
                Auth::id(),
                'reject_event',
                'events',
                $eventId,
                ['approved' => $event->approved],
                ['approved' => -1, 'rejection_reason' => $request->reason]
            );

            return redirect()->back()->with('warning', 'Sự kiện đã bị từ chối!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi từ chối sự kiện: '.$e->getMessage());
        }
    }

    /**
     * Duyệt yêu cầu hủy sự kiện
     */
    public function approveCancellation(Request $request, $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();

            if (! $event->cancellation_requested) {
                return redirect()->back()->with('error', 'Sự kiện này không có yêu cầu hủy.');
            }

            $event->update([
                'status' => 'cancelled',
                'cancellation_requested' => false,
            ]);

            // Gửi notification cho organizer
            $this->notificationService->notifyCancellationApproved(
                $event->organizer_id,
                $event->title
            );

            // Gửi notification cho tất cả attendees đã mua vé
            $this->notificationService->notifyAttendeesEventCancelled(
                $event->event_id,
                $event->title
            );

            AdminLog::logAction(
                Auth::id(),
                'approve_cancellation',
                'events',
                $eventId,
                ['status' => $event->status, 'cancellation_requested' => true],
                ['status' => 'cancelled', 'cancellation_requested' => false]
            );

            return redirect()->back()->with('success', 'Yêu cầu hủy sự kiện đã được duyệt!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi duyệt yêu cầu hủy: '.$e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu hủy sự kiện
     */
    public function rejectCancellation(Request $request, $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();

            if (! $event->cancellation_requested) {
                return redirect()->back()->with('error', 'Sự kiện này không có yêu cầu hủy.');
            }

            $event->update([
                'cancellation_requested' => false,
                'cancellation_reason' => null,
                'cancellation_requested_at' => null,
            ]);

            // Gửi notification cho organizer
            $this->notificationService->notifyCancellationRejected(
                $event->organizer_id,
                $event->title
            );

            AdminLog::logAction(
                Auth::id(),
                'reject_cancellation',
                'events',
                $eventId,
                ['cancellation_requested' => true],
                ['cancellation_requested' => false]
            );

            return redirect()->back()->with('success', 'Yêu cầu hủy sự kiện đã bị từ chối!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi từ chối yêu cầu hủy: '.$e->getMessage());
        }
    }

    /**
     * Xóa sự kiện (soft delete)
     */
    public function deleteEvent(Request $request, $eventId)
    {
        try {
            $event = Event::where('event_id', $eventId)->firstOrFail();

            $event->delete(); // Soft delete

            AdminLog::logAction(
                Auth::id(),
                'delete_event',
                'events',
                $eventId,
                ['deleted_at' => null],
                ['deleted_at' => now()]
            );

            return redirect()->back()->with('success', 'Sự kiện đã được xóa!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa sự kiện: '.$e->getMessage());
        }
    }

    /**
     * Quản lý users
     */
    public function users(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Cập nhật role user
     */
    public function updateUserRole(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,role_id',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu cập nhật role không hợp lệ!');
        }
        $user = User::findOrFail($userId);
        $oldRoles = $user->roles->pluck('role_id')->toArray();

        // Sync với role mới (thêm role mới, giữ các role cũ)
        if (! in_array($request->role_id, $oldRoles)) {
            $user->roles()->attach($request->role_id);
        }

        AdminLog::logAction(
            Auth::id(),
            'update_user_role',
            'users',
            $userId,
            ['roles' => $oldRoles],
            ['roles' => array_merge($oldRoles, [$request->role_id])]
        );

        return redirect()->back()->with('success', 'Cập nhật role thành công!');
    }

    /**
     * Xóa user
     */
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->isAdmin()) {
            return redirect()->back()->with('warning', 'Không thể xóa user admin!');
        }
        $userData = $user->toArray();
        $user->delete();
        AdminLog::logAction(
            Auth::id(),
            'delete_user',
            'users',
            $userId,
            $userData,
            null
        );

        return redirect()->back()->with('success', 'Xóa user thành công!');
    }

    /**
     * Quản lý refunds
     */
    public function refunds(Request $request)
    {
        $query = Refund::with(['payment.ticket.ticketType.event', 'payment.ticket.attendee', 'requester']);

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.refunds', compact('refunds'));
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, $refundId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu refund không hợp lệ!');
        }
        $refund = Refund::with(['payment.ticket.ticketType.event', 'requester'])->findOrFail($refundId);
        $oldStatus = $refund->status;
        $refund->update([
            'status' => $request->status,
            'processed_at' => now(),
            'admin_notes' => $request->admin_notes ?? null,
        ]);

        // Gửi notification cho user về kết quả refund
        try {
            $event = $refund->payment->ticket->ticketType->event;
            $actionUrl = route('tickets.show', $refund->payment->ticket->ticket_id);
            
            $this->notificationService->notifyRefundStatus(
                $refund->requester_id,
                $event->title ?? $event->event_name,
                $request->status,
                $refund->payment->amount,
                $actionUrl
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify user about refund status', [
                'error' => $e->getMessage(),
                'refund_id' => $refundId,
            ]);
        }

        // Nếu refund được duyệt, cập nhật payment status
        if ($request->status === 'approved') {
            $refund->payment->update(['status' => 'refunded']);
        }

        AdminLog::logAction(
            Auth::id(),
            'process_refund',
            'refunds',
            $refundId,
            ['status' => $oldStatus],
            ['status' => $request->status]
        );

        return redirect()->back()->with('success', 'Xử lý refund thành công!');
    }

    /**
     * Xem admin logs
     */
    public function logs()
    {
        $logs = AdminLog::with(['admin', 'user'])
            ->orderBy('log_id', 'desc')
            ->paginate(20);

        return view('admin.logs', compact('logs'));
    }

    /**
     * Xem chi tiết log
     */
    public function showLog($logId)
    {
        $log = AdminLog::with(['admin', 'user'])->findOrFail($logId);

        return response()->json([
            'log_id' => $log->log_id,
            'action' => $log->action,
            'action_description' => $log->action_description ?? $log->action,
            'admin_id' => $log->admin_id,
            'admin_name' => $log->admin ? ($log->admin->full_name ?? $log->admin->name) : null,
            'user_id' => $log->user_id,
            'user_name' => $log->user ? ($log->user->full_name ?? $log->user->name) : null,
            'target_table' => $log->target_table,
            'target_id' => $log->target_id,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Quản lý payments
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['ticket.ticketType.event', 'ticket.attendee', 'paymentMethod']);

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_id', 'like', '%'.$request->search.'%')
                    ->orWhereHas('ticket.attendee', function ($q) use ($request) {
                        $q->where('full_name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    });
            });
        }

        $payments = $query->orderBy('payment_id', 'desc')->paginate(20);

        return view('admin.payments', compact('payments'));
    }

    /**
     * Quản lý tickets
     */
    public function tickets(Request $request)
    {
        $query = Ticket::with(['ticketType.event', 'attendee', 'payment']);

        if ($request->filled('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('qr_code', 'like', '%'.$request->search.'%')
                    ->orWhereHas('attendee', function ($q) use ($request) {
                        $q->where('full_name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('ticketType.event', function ($q) use ($request) {
                        $q->where('title', 'like', '%'.$request->search.'%');
                    });
            });
        }

        $tickets = $query->orderBy('ticket_id', 'desc')->paginate(20);

        return view('admin.tickets', compact('tickets'));
    }

    /**
     * Quản lý categories
     */
    public function categories()
    {
        $categories = \App\Models\EventCategory::withCount('events')->orderBy('category_name')->get();

        return view('admin.categories', compact('categories'));
    }

    /**
     * Tạo category mới
     */
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100|unique:event_categories,category_name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = \App\Models\EventCategory::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
        ]);

        AdminLog::logAction(
            Auth::id(),
            'create_category',
            'event_categories',
            $category->category_id,
            null,
            $category->toArray()
        );

        return redirect()->back()->with('success', 'Tạo danh mục thành công!');
    }

    /**
     * Cập nhật category
     */
    public function updateCategory(Request $request, $categoryId)
    {
        $category = \App\Models\EventCategory::where('category_id', $categoryId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100|unique:event_categories,category_name,'.$categoryId.',category_id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $category->toArray();
        $category->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
        ]);

        AdminLog::logAction(
            Auth::id(),
            'update_category',
            'event_categories',
            $categoryId,
            $oldData,
            $category->toArray()
        );

        return redirect()->back()->with('success', 'Cập nhật danh mục thành công!');
    }

    /**
     * Xóa category
     */
    public function deleteCategory($categoryId)
    {
        $category = \App\Models\EventCategory::where('category_id', $categoryId)->firstOrFail();
        $categoryData = $category->toArray();

        // Kiểm tra xem có sự kiện nào đang dùng category này không
        if ($category->events()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa danh mục này vì đang có sự kiện sử dụng!');
        }

        $category->delete();

        AdminLog::logAction(
            Auth::id(),
            'delete_category',
            'event_categories',
            $categoryId,
            $categoryData,
            null
        );

        return redirect()->back()->with('success', 'Xóa danh mục thành công!');
    }

    /**
     * Quản lý locations
     */
    public function locations()
    {
        $locations = \App\Models\EventLocation::withCount('events')->orderBy('name')->get();

        return view('admin.locations', compact('locations'));
    }

    /**
     * Tạo location mới
     */
    public function createLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $location = \App\Models\EventLocation::create([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'capacity' => $request->capacity ?? 0,
        ]);

        AdminLog::logAction(
            Auth::id(),
            'create_location',
            'event_locations',
            $location->location_id,
            null,
            $location->toArray()
        );

        return redirect()->back()->with('success', 'Tạo địa điểm thành công!');
    }

    /**
     * Cập nhật location
     */
    public function updateLocation(Request $request, $locationId)
    {
        $location = \App\Models\EventLocation::where('location_id', $locationId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $oldData = $location->toArray();
        $location->update([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'capacity' => $request->capacity ?? 0,
        ]);

        AdminLog::logAction(
            Auth::id(),
            'update_location',
            'event_locations',
            $locationId,
            $oldData,
            $location->toArray()
        );

        return redirect()->back()->with('success', 'Cập nhật địa điểm thành công!');
    }

    /**
     * Xóa location
     */
    public function deleteLocation($locationId)
    {
        $location = \App\Models\EventLocation::where('location_id', $locationId)->firstOrFail();
        $locationData = $location->toArray();

        // Kiểm tra xem có sự kiện nào đang dùng location này không
        if ($location->events()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa địa điểm này vì đang có sự kiện sử dụng!');
        }

        $location->delete();

        AdminLog::logAction(
            Auth::id(),
            'delete_location',
            'event_locations',
            $locationId,
            $locationData,
            null
        );

        return redirect()->back()->with('success', 'Xóa địa điểm thành công!');
    }
}
