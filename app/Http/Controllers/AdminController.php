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
use Illuminate\Support\Facades\DB;
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
        $pendingEvents = Event::with(['category', 'location', 'organizer'])
            ->where('approved', 0)
            ->latest()
            ->take(10)
            ->get();

        $recentPayments = Payment::with(['ticket.ticketType.event', 'ticket.attendee', 'paymentMethod'])
            ->where('status', 'success')
            ->latest('paid_at')
            ->take(10)
            ->get();

        $eventsRevenue = Event::with(['organizer'])
            ->select('events.*')
            ->leftJoin('ticket_types', 'events.event_id', '=', 'ticket_types.event_id')
            ->leftJoin('tickets', 'ticket_types.ticket_type_id', '=', 'tickets.ticket_type_id')
            ->leftJoin('payments', function ($join) {
                $join->on('tickets.ticket_id', '=', 'payments.ticket_id')
                    ->where('payments.status', '=', 'success');
            })
            ->selectRaw('events.*, COALESCE(SUM(payments.amount), 0) as payments_sum_amount')
            ->groupBy('events.event_id')
            ->havingRaw('COALESCE(SUM(payments.amount), 0) > 0')
            ->orderByDesc('payments_sum_amount')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('pendingEvents', 'recentPayments', 'eventsRevenue'));
    }

    /**
     * Quản lý events - approve/reject
     */
    public function events(Request $request)
    {
        $query = Event::with(['organizer', 'category', 'location']);

        if ($request->filled('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('organizer', function ($q) use ($search) {
                        $q->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('approval_status') && $request->approval_status != '') {
            if ($request->approval_status == 'pending') {
                $query->where('approved', 0);
            } elseif ($request->approval_status == 'approved') {
                $query->where('approved', 1);
            } elseif ($request->approval_status == 'rejected') {
                $query->where('approved', -1);
            } elseif ($request->approval_status == 'cancellation') {
                $query->where('cancellation_requested', true)->where('status', '!=', 'cancelled');
            }
        }

        if ($request->filled('event_status') && $request->event_status != '') {
            $query->where('status', $request->event_status);
        }

        $events = $query->latest()->paginate(15);

        // Tính doanh thu bán vé hiện tại cho mỗi event (từ payments thành công)
        foreach ($events as $event) {
            $event->revenue = Payment::whereHas('ticket.ticketType', function ($q) use ($event) {
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

            $this->notificationService->notifyCancellationApproved(
                $event->organizer_id,
                $event->title
            );

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

        if ($request->filled('role_id') && $request->role_id != '') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.role_id', $request->role_id);
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = \App\Models\Role::orderBy('role_name')->get();

        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Xem chi tiết user và các liên kết liên quan
     */
    public function showUser($userId)
    {
        $user = User::with([
            'roles',
            'organizedEvents' => function ($query) {
                $query->with(['category', 'location'])->latest();
            },
            'tickets' => function ($query) {
                $query->with(['ticketType.event', 'payment.paymentMethod'])->latest('purchase_time');
            },
            'reviews' => function ($query) {
                $query->with('event')->latest();
            },
            'favoriteEvents' => function ($query) {
                $query->with(['category', 'location'])->latest();
            },
        ])->findOrFail($userId);

        $payments = Payment::whereHas('ticket', function ($query) use ($userId) {
            $query->where('attendee_id', $userId);
        })
            ->with(['ticket.ticketType.event', 'paymentMethod'])
            ->latest('paid_at')
            ->paginate(10);

        $totalRevenue = Payment::whereHas('ticket', function ($query) use ($userId) {
            $query->where('attendee_id', $userId);
        })
            ->where('status', 'success')
            ->sum('amount');

        $totalTickets = $user->tickets()->count();
        $totalEvents = $user->organizedEvents()->count();
        $totalReviews = $user->reviews()->count();

        return view('admin.users.show', compact(
            'user',
            'payments',
            'totalRevenue',
            'totalTickets',
            'totalEvents',
            'totalReviews'
        ));
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
    public function deleteUser($user)
    {
        try {
            $user = User::findOrFail($user);

            if ($user->isAdmin()) {
                return redirect()->back()->with('warning', 'Không thể xóa user admin!');
            }

            $userId = $user->user_id;
            $userData = $user->toArray();

            DB::transaction(function () use ($user, $userId) {
                DB::table('user_roles')->where('user_id', $userId)->delete();
                DB::table('sessions')->where('user_id', $userId)->delete();
                DB::table('favorites')->where('user_id', $userId)->delete();
                DB::table('notifications')->where('user_id', $userId)->delete();

                $reviewIds = DB::table('reviews')->where('user_id', $userId)->pluck('review_id');
                if ($reviewIds->isNotEmpty()) {
                    DB::table('review_reports')->whereIn('review_id', $reviewIds)->delete();
                }

                DB::table('reviews')->where('user_id', $userId)->delete();

                $ticketIds = DB::table('tickets')->where('attendee_id', $userId)->pluck('ticket_id');
                if ($ticketIds->isNotEmpty()) {
                    $paymentIds = DB::table('payments')->whereIn('ticket_id', $ticketIds)->pluck('payment_id');

                    if ($paymentIds->isNotEmpty()) {
                        DB::table('refunds')->whereIn('payment_id', $paymentIds)->delete();
                    }

                    DB::table('payments')->whereIn('ticket_id', $ticketIds)->delete();
                    DB::table('tickets')->where('attendee_id', $userId)->delete();
                }

                DB::table('refunds')->where('requester_id', $userId)->delete();
                DB::table('incident_reports')->where('reporter_id', $userId)->delete();

                DB::table('events')->where('organizer_id', $userId)->update(['organizer_id' => null]);
                DB::table('events')->where('approved_by', $userId)->update(['approved_by' => null]);

                $user->delete();
            });

            AdminLog::logAction(
                Auth::id(),
                'delete_user',
                'users',
                $userId,
                $userData,
                null
            );

            return redirect()->back()->with('success', 'Xóa user thành công!');
        } catch (\Exception $e) {
            Log::error('Failed to delete user: '.$e->getMessage());

            return redirect()->back()->with('error', 'Lỗi khi xóa user: '.$e->getMessage());
        }
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
