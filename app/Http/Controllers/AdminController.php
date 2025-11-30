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
        $stats = [
            'total_users' => User::count(),
            'total_events' => Event::count(),
            'pending_events' => Event::where('status', 'pending')->count(),
            'total_tickets' => Ticket::count(),
            'total_revenue' => Payment::where('status', 'success')->sum('amount'),
            'recent_events' => Event::with(['organizer'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_users' => User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
        // return response()->json($stats);
    }

    /**
     * Quản lý events - approve/reject
     */
    public function events(Request $request)
    {
        $query = Event::with(['organizer', 'category', 'location']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->paginate(15);

        return view('admin.events', compact('events'));
    }

    /**
     * Approve event
     */
    public function approveEvent($eventId)
    {
        $event = Event::findOrFail($eventId);

        $event->update(['approved' => true, 'approved_at' => now(), 'approved_by' => Auth::id()]);

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
            ['approved' => false],
            ['approved' => true]
        );

        return response()->json([
            'message' => 'Event approved successfully',
            'event' => $event,
        ]);
    }

    /**
     * Reject event
     */
    public function rejectEvent(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $event = Event::findOrFail($eventId);

        $event->update([
            'approved' => false,
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
            ['approved' => null],
            ['approved' => false, 'rejection_reason' => $request->reason]
        );

        return response()->json([
            'message' => 'Event rejected successfully',
            'event' => $event,
        ]);
    }

    /**
     * Quản lý users
     */
    public function users(Request $request)
    {
        $query = User::with(['roles']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Cập nhật role user
     */
    public function updateUserRole(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,role_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($userId);
        $oldRoles = $user->roles->pluck('role_id')->toArray();

        $user->roles()->sync($request->role_ids);

        AdminLog::logAction(
            Auth::id(),
            'update_user_role',
            'users',
            $userId,
            ['roles' => $oldRoles],
            ['roles' => $request->role_ids]
        );

        return response()->json([
            'message' => 'User roles updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Xóa user
     */
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        // Không cho phép xóa admin
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Cannot delete admin user',
            ], 403);
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

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Quản lý refunds
     */
    public function refunds()
    {
        $refunds = Refund::with(['payment.ticket.ticketType.event', 'payment.ticket.attendee', 'requester'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $refund = Refund::findOrFail($refundId);

        $refund->update([
            'status' => $request->status,
            'processed_at' => now(),
        ]);

        AdminLog::logAction(
            Auth::id(),
            'process_refund',
            'refunds',
            $refundId,
            ['status' => 'pending'],
            ['status' => $request->status]
        );

        return response()->json([
            'message' => 'Refund processed successfully',
            'refund' => $refund,
        ]);
    }

    /**
     * Xem admin logs
     */
    public function logs()
    {
        $logs = AdminLog::with(['admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.logs', compact('logs'));
    }
}
