<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Mail\TicketInfoMail;
use App\Models\AdminLog;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventLocation;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\ImageUploadService;
use App\Services\NotificationService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends WelcomeController
{
    protected NotificationService $notificationService;

    protected QRCodeService $qrCodeService;

    protected ImageUploadService $imageUploadService;

    public function __construct(NotificationService $notificationService, QRCodeService $qrCodeService, ImageUploadService $imageUploadService)
    {
        $this->notificationService = $notificationService;
        $this->qrCodeService = $qrCodeService;
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Lấy danh sách events với filtering và pagination
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'location', 'organizer'])
            ->where('approved', 1)
            ->where('status', '!=', 'cancelled');

        if (!$request->filled('status') || $request->status == '') {
            $query->where('end_time', '>=', now());
        }

        if ($request->filled('status') && $request->status != '') {
            $now = now();
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_time', '>', $now);
                    break;
                case 'ongoing':
                    $query->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                    break;
                case 'ended':
                    $query->where('end_time', '<', $now);
                    break;
            }
        }

        if ($request->filled('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('location_id') && $request->location_id != '') {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('search') && $request->search != '') {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('start_date') && $request->start_date != '') {
            $query->where('start_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date') && $request->end_date != '') {
            $query->where('end_time', '<=', $request->end_date);
        }

        $events = $query->orderBy('start_time', 'asc')->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Xem chi tiết event
     */
    public function show($id)
    {
        $event = Event::with([
            'category',
            'location',
            'organizer',
            'ticketTypes',
            'reviews.user',
            'tags',
        ])->where('event_id', $id)->firstOrFail();

        return view('events.show', compact('event'));
    }

    /**
     * Hiển thị form tạo event
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Tạo event mới (Organizer only)
     */
    public function store(StoreEventRequest $request)
    {
        $bannerUrl = $request->banner_url;

        if ($request->hasFile('banner_image')) {
            try {
                $bannerPath = $this->imageUploadService->uploadEventBanner($request->file('banner_image'));
                $bannerUrl = $this->imageUploadService->getUrl($bannerPath);
            } catch (\Exception $e) {
                Log::error('Failed to upload event banner: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Lỗi khi upload ảnh banner: ' . $e->getMessage());
            }
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'category_id' => $request->category_id,
            'location_id' => $request->location_id,
            'organizer_id' => Auth::id(),
            'max_attendees' => $request->max_attendees,
            'banner_url' => $bannerUrl,
            'approved' => 0,
        ]);

        try {
            $organizer = Auth::user();
            $this->notificationService->notifyAdminNewEvent(
                $event->event_id,
                $event->title,
                $organizer->full_name ?? $organizer->name ?? 'Người dùng'
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about new event', [
                'error' => $e->getMessage(),
                'event_id' => $event->event_id,
            ]);
        }

        if ($request->has('ticket_types') && is_array($request->ticket_types)) {
            foreach ($request->ticket_types as $ticketTypeData) {
                if (isset($ticketTypeData['_delete'])) {
                    continue;
                }

                \App\Models\TicketType::create([
                    'event_id' => $event->event_id,
                    'name' => $ticketTypeData['name'],
                    'price' => $ticketTypeData['price'],
                    'total_quantity' => $ticketTypeData['total_quantity'],
                    'remaining_quantity' => $ticketTypeData['total_quantity'],
                    'description' => $ticketTypeData['description'] ?? null,
                    'sale_start_time' => ! empty($ticketTypeData['sale_start_time']) ? $ticketTypeData['sale_start_time'] : null,
                    'sale_end_time' => ! empty($ticketTypeData['sale_end_time']) ? $ticketTypeData['sale_end_time'] : null,
                    'is_active' => isset($ticketTypeData['is_active']) && $ticketTypeData['is_active'] == '1',
                ]);
            }
        }

        try {
            AdminLog::logUserAction(null, 'create_event', 'events', $event->event_id, null, [
                'title' => $event->title,
                'organizer_id' => $event->organizer_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log create event action', ['error' => $e->getMessage()]);
        }

        return redirect()->route('events.my')->with('success', 'Tạo sự kiện thành công! Sự kiện đang chờ duyệt.');
    }

    /**
     * Hiển thị form chỉnh sửa event
     */
    public function edit(Request $request, $id)
    {
        $event = $request->event;
        $event->load('ticketTypes');

        return view('events.edit', compact('event'));
    }

    /**
     * Cập nhật event (Owner only)
     */
    public function update(Request $request, $id)
    {
        $event = $request->event;

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'description' => 'sometimes|string',
            'start_time' => ['sometimes', 'date', function ($attribute, $value, $fail) {
                if ($value && strtotime($value) <= time()) {
                    $fail('Thời gian bắt đầu phải sau thời gian hiện tại.');
                }
            }],
            'end_time' => ['sometimes', 'date', function ($attribute, $value, $fail) use ($request, $event) {
                $startTime = $request->has('start_time') ? $request->start_time : $event->start_time->format('Y-m-d H:i:s');
                if ($value && strtotime($value) <= strtotime($startTime)) {
                    $fail('Thời gian kết thúc phải sau thời gian bắt đầu.');
                }
            }],
            'category_id' => 'sometimes|exists:event_categories,category_id',
            'location_id' => 'sometimes|exists:event_locations,location_id',
            'max_attendees' => 'sometimes|integer|min:1',
            'banner_url' => 'nullable|url',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Dữ liệu cập nhật sự kiện không hợp lệ!');
        }

        $oldValues = $event->only(['title', 'description', 'start_time', 'end_time', 'category_id', 'location_id', 'max_attendees', 'banner_url']);

        $updateData = $request->only([
            'title', 'description', 'start_time', 'end_time',
            'category_id', 'location_id', 'max_attendees', 'banner_url',
        ]);

        if ($request->hasFile('banner_image')) {
            try {
                if ($event->banner_url) {
                    $storageUrl = config('filesystems.disks.public.url') ?: url('/storage');
                    if (strpos($event->banner_url, $storageUrl) === 0) {
                        $oldPath = str_replace($storageUrl, '', $event->banner_url);
                        $oldPath = ltrim($oldPath, '/'); // Remove leading slash
                        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                            $this->imageUploadService->delete($oldPath);
                        }
                    }
                }

                $bannerPath = $this->imageUploadService->uploadEventBanner($request->file('banner_image'));
                $updateData['banner_url'] = $this->imageUploadService->getUrl($bannerPath);
            } catch (\Exception $e) {
                Log::error('Failed to upload event banner: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Lỗi khi upload ảnh banner: ' . $e->getMessage());
            }
        }

        $event->update($updateData);

        if ($request->has('ticket_types') && is_array($request->ticket_types)) {
            $existingTicketTypeIds = [];

            foreach ($request->ticket_types as $index => $ticketTypeData) {
                if (isset($ticketTypeData['_delete'])) {
                    if (isset($ticketTypeData['ticket_type_id'])) {
                        $ticketType = \App\Models\TicketType::find($ticketTypeData['ticket_type_id']);
                        if ($ticketType && $ticketType->tickets()->count() == 0) {
                            $ticketType->delete();
                        }
                    }

                    continue;
                }

                if (isset($ticketTypeData['ticket_type_id'])) {
                    $ticketType = \App\Models\TicketType::find($ticketTypeData['ticket_type_id']);
                    if ($ticketType) {
                        $oldQuantity = $ticketType->total_quantity;
                        $newQuantity = $ticketTypeData['total_quantity'];

                        $remainingQuantity = $ticketType->remaining_quantity;
                        if ($newQuantity != $oldQuantity) {
                            $soldQuantity = $oldQuantity - $remainingQuantity;
                            $remainingQuantity = max(0, $newQuantity - $soldQuantity);
                        }

                        $ticketType->update([
                            'name' => $ticketTypeData['name'],
                            'price' => $ticketTypeData['price'],
                            'total_quantity' => $newQuantity,
                            'remaining_quantity' => $remainingQuantity,
                            'description' => $ticketTypeData['description'] ?? null,
                            'sale_start_time' => ! empty($ticketTypeData['sale_start_time']) ? $ticketTypeData['sale_start_time'] : null,
                            'sale_end_time' => ! empty($ticketTypeData['sale_end_time']) ? $ticketTypeData['sale_end_time'] : null,
                            'is_active' => isset($ticketTypeData['is_active']) && $ticketTypeData['is_active'] == '1',
                        ]);
                        $existingTicketTypeIds[] = $ticketTypeData['ticket_type_id'];
                    }
                } else {
                    $newTicketType = \App\Models\TicketType::create([
                        'event_id' => $event->event_id,
                        'name' => $ticketTypeData['name'],
                        'price' => $ticketTypeData['price'],
                        'total_quantity' => $ticketTypeData['total_quantity'],
                        'remaining_quantity' => $ticketTypeData['total_quantity'],
                        'description' => $ticketTypeData['description'] ?? null,
                        'sale_start_time' => ! empty($ticketTypeData['sale_start_time']) ? $ticketTypeData['sale_start_time'] : null,
                        'sale_end_time' => ! empty($ticketTypeData['sale_end_time']) ? $ticketTypeData['sale_end_time'] : null,
                        'is_active' => isset($ticketTypeData['is_active']) && $ticketTypeData['is_active'] == '1',
                    ]);
                    $existingTicketTypeIds[] = $newTicketType->ticket_type_id;
                }
            }
        }

        try {
            AdminLog::logUserAction(null, 'update_event', 'events', $event->event_id, $oldValues, $event->only(['title', 'description', 'start_time', 'end_time', 'category_id', 'location_id', 'max_attendees', 'banner_url']));
        } catch (\Exception $e) {
            Log::error('Failed to log update event action', ['error' => $e->getMessage()]);
        }

        return redirect()->route('events.my')->with('success', 'Cập nhật sự kiện thành công!');
    }

    /**
     * Xóa event (Owner only)
     */
    public function destroy(Request $request, $id)
    {
        $event = $request->event; // Từ middleware event.owner

        $event->delete();

        return redirect()->route('events.my')->with('success', 'Xóa sự kiện thành công!');
    }

    /**
     * Dashboard cho organizer
     */
    public function dashboard()
    {
        $organizerId = Auth::id();

        // Thống kê tổng quan
        $totalEvents = Event::where('organizer_id', $organizerId)->count();
        $approvedEvents = Event::where('organizer_id', $organizerId)->where('approved', 1)->count();
        $pendingEvents = Event::where('organizer_id', $organizerId)->where('approved', 0)->count();

        // Tổng số thanh toán tiền mặt chờ xác nhận
        $totalPendingCashPayments = Payment::whereHas('ticket.ticketType.event', function ($query) use ($organizerId) {
            $query->where('organizer_id', $organizerId);
        })
            ->whereHas('paymentMethod', function ($query) {
                $query->where('name', 'Tiền mặt');
            })
            ->where('status', 'failed')
            ->whereHas('ticket', function ($query) {
                $query->where('payment_status', 'pending');
            })
            ->count();

        // Tổng số vé đã bán (đã thanh toán) - tính theo quantity
        $totalTicketsSold = Ticket::whereHas('ticketType.event', function ($query) use ($organizerId) {
            $query->where('organizer_id', $organizerId);
        })
            ->where('payment_status', 'paid')
            ->sum('quantity');

        // Doanh thu (từ payments thành công)
        $totalRevenue = Payment::whereHas('ticket.ticketType.event', function ($query) use ($organizerId) {
            $query->where('organizer_id', $organizerId);
        })
            ->where('status', 'success')
            ->sum('amount');

        // Sự kiện sắp diễn ra (tất cả sự kiện chưa bắt đầu)
        $upcomingEvents = Event::where('organizer_id', $organizerId)
            ->where('approved', 1)
            ->where('start_time', '>', now())
            ->with(['category', 'location'])
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        // Vé đã bán gần đây
        $recentTickets = Ticket::whereHas('ticketType.event', function ($query) use ($organizerId) {
            $query->where('organizer_id', $organizerId);
        })
            ->where('payment_status', 'paid')
            ->with(['ticketType.event', 'attendee'])
            ->orderBy('purchase_time', 'desc')
            ->take(5)
            ->get();

        // Doanh thu theo từng sự kiện
        $eventsRevenue = Event::where('organizer_id', $organizerId)
            ->with(['organizer', 'category'])
            ->get()
            ->map(function ($event) {
                $event->revenue = Payment::whereHas('ticket.ticketType', function ($query) use ($event) {
                    $query->where('event_id', $event->event_id);
                })
                    ->where('status', 'success')
                    ->sum('amount');

                return $event;
            })
            ->filter(function ($event) {
                return $event->revenue > 0;
            })
            ->sortByDesc('revenue')
            ->take(10);

        // Sự kiện có nhiều thanh toán chờ xác nhận nhất
        $eventsWithPendingPayments = Event::where('organizer_id', $organizerId)
            ->get()
            ->map(function ($event) {
                $event->pending_cash_payments_count = Payment::whereHas('ticket.ticketType', function ($query) use ($event) {
                    $query->where('event_id', $event->event_id);
                })
                ->whereHas('paymentMethod', function ($query) {
                    $query->where('name', 'Tiền mặt');
                })
                ->where('status', 'failed')
                ->whereHas('ticket', function ($query) {
                    $query->where('payment_status', 'pending');
                })
                ->count();

                return $event;
            })
            ->filter(function ($event) {
                return $event->pending_cash_payments_count > 0;
            })
            ->sortByDesc('pending_cash_payments_count')
            ->take(5);

        return view('events.dashboard', compact(
            'totalEvents',
            'approvedEvents',
            'pendingEvents',
            'totalPendingCashPayments',
            'totalTicketsSold',
            'totalRevenue',
            'upcomingEvents',
            'recentTickets',
            'eventsWithPendingPayments',
            'eventsRevenue'
        ));
    }

    /**
     * Lấy events do user tổ chức
     */
    public function myEvents(Request $request)
    {
        $query = Event::with(['category', 'location'])
            ->where('organizer_id', Auth::id());

        // Filter: ended events
        if ($request->has('status') && $request->status == 'ended') {
            $query->where('end_time', '<', now());
        } elseif ($request->has('status') && $request->status == 'cancelled') {
            $query->where('status', 'cancelled');
        } else {
            // Mặc định hiển thị tất cả
        }

        $events = $query->orderBy('created_at', 'desc')
            ->paginate(12);

        $events->getCollection()->transform(function ($event) {
            $event->pending_cash_payments_count = $event->pending_cash_payments_count;

            return $event;
        });

        // Tính số sự kiện đã kết thúc
        $endedEventsCount = Event::where('organizer_id', Auth::id())
            ->where('end_time', '<', now())
            ->count();

        // Tính số sự kiện đã hủy
        $cancelledEventsCount = Event::where('organizer_id', Auth::id())
            ->where('status', 'cancelled')
            ->count();

        return view('events.my', compact('events', 'endedEventsCount', 'cancelledEventsCount'));
    }

    /**
     * Gửi yêu cầu hủy sự kiện (Event Owner only)
     */
    public function requestCancellation(Request $request, $id)
    {
        $event = $request->event;

        if ($event->status === 'cancelled') {
            return redirect()->back()->with('error', 'Sự kiện đã bị hủy.');
        }

        if ($event->cancellation_requested) {
            return redirect()->back()->with('warning', 'Yêu cầu hủy sự kiện đang chờ admin xử lý.');
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ], [
            'cancellation_reason.required' => 'Vui lòng nhập lý do hủy sự kiện.',
            'cancellation_reason.min' => 'Lý do hủy phải có ít nhất 10 ký tự.',
            'cancellation_reason.max' => 'Lý do hủy không được vượt quá 1000 ký tự.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Dữ liệu không hợp lệ!');
        }

        try {
            if (! $event->relationLoaded('organizer')) {
                $event->load('organizer');
            }

            $event->update([
                'cancellation_requested' => true,
                'cancellation_reason' => $request->cancellation_reason,
                'cancellation_requested_at' => now(),
            ]);

            try {
                $this->notificationService->notifyAdminCancellationRequest(
                    $event->event_id,
                    $event->title,
                    $event->organizer->full_name ?? $event->organizer->name ?? 'Người dùng',
                    $request->cancellation_reason
                );
            } catch (\Exception $e) {
                Log::error('Failed to notify admins about cancellation request', [
                    'error' => $e->getMessage(),
                    'event_id' => $event->event_id,
                ]);
                // Không throw error, chỉ log lại để không ảnh hưởng đến việc tạo request
            }

            try {
                AdminLog::logUserAction(null, 'request_cancellation', 'events', $event->event_id, null, [
                    'cancellation_requested' => true,
                    'cancellation_reason' => $request->cancellation_reason,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log cancellation request action', ['error' => $e->getMessage()]);
            }

            return redirect()->back()->with('success', 'Yêu cầu hủy sự kiện đã được gửi. Vui lòng chờ admin xác nhận.');
        } catch (\Exception $e) {
            Log::error('Error requesting cancellation: '.$e->getMessage());

            return redirect()->back()->with('error', 'Lỗi khi gửi yêu cầu hủy: '.$e->getMessage());
        }
    }

    /**
     * Lấy danh sách categories
     */
    public function categories()
    {
        $categories = EventCategory::all();

        return view('categories.index', compact('categories'));
    }

    /**
     * Lấy danh sách locations
     */
    public function locations()
    {
        $locations = EventLocation::all();

        return view('locations.index', compact('locations'));
    }

    /**
     * Hiển thị danh sách thanh toán tiền mặt chờ xác nhận (Event Owner only)
     */
    public function pendingCashPayments(Request $request, $eventId)
    {
        if ($request->has('event') && is_object($request->event)) {
            $event = $request->event;
        } else {
            $event = Event::where('event_id', $eventId)->firstOrFail();
        }

        $pendingPayments = Payment::with([
            'ticket.attendee',
            'ticket.ticketType',
            'paymentMethod',
        ])
            ->whereHas('ticket.ticketType', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->whereHas('paymentMethod', function ($query) {
                $query->where('name', 'Tiền mặt');
            })
            ->where('status', 'failed')
            ->whereHas('ticket', function ($query) {
                $query->where('payment_status', 'pending');
            })
            ->orderBy('ticket_id', 'desc')
            ->paginate(15);

        return view('events.pending-cash-payments', compact('event', 'pendingPayments'));
    }

    /**
     * Xác nhận thanh toán tiền mặt (Event Owner only)
     */
    public function confirmCashPayment(Request $request, $paymentId)
    {
        $payment = Payment::with(['ticket.ticketType.event', 'paymentMethod'])
            ->where('payment_id', $paymentId)
            ->firstOrFail();

        if (($payment->paymentMethod->name ?? '') !== 'Tiền mặt') {
            return redirect()->back()->with('error', 'Chỉ có thể xác nhận thanh toán tiền mặt.');
        }

        $event = $payment->ticket->ticketType->event;
        $user = Auth::user();
        if (! $user->isAdmin() && $event->organizer_id !== $user->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền xác nhận thanh toán này.');
        }

        if ($payment->status !== 'failed' || $payment->ticket->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Thanh toán này không thể xác nhận.');
        }

        try {
            DB::beginTransaction();

            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);

            $ticket = $payment->ticket;
            $ticket->update([
                'payment_status' => 'paid',
            ]);

            $ticket->load(['ticketType.event', 'attendee']);

            DB::commit();

            try {
                $this->notificationService->notifyPaymentConfirmed(
                    $ticket->attendee_id,
                    $event->title ?? $event->event_name,
                    $ticket->ticket_id,
                    $payment->amount
                );
                Log::info('Notification sent successfully for payment confirmation', [
                    'user_id' => $ticket->attendee_id,
                    'ticket_id' => $ticket->ticket_id,
                    'event' => $event->title ?? $event->event_name,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification for payment confirmation', [
                    'error' => $e->getMessage(),
                    'user_id' => $ticket->attendee_id,
                    'ticket_id' => $ticket->ticket_id,
                ]);
            }

            try {
                $qrCode = $ticket->qr_code ?? $this->qrCodeService->generateQRCode($ticket);
                $qrImageUrl = $this->qrCodeService->generateQRCodeUrl($qrCode);

                Mail::to($ticket->attendee->email)->send(new TicketInfoMail($ticket, $qrCode, $qrImageUrl));
            } catch (\Exception $e) {
                Log::error('Failed to send ticket info email: '.$e->getMessage());
                // Không throw error, chỉ log lại
            }

            return redirect()->back()->with('success', 'Xác nhận thanh toán thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming cash payment: '.$e->getMessage());

            return redirect()->back()->with('error', 'Lỗi khi xác nhận thanh toán: '.$e->getMessage());
        }
    }

    /**
     * Từ chối thanh toán tiền mặt (Event Owner only)
     */
    public function rejectCashPayment(Request $request, $paymentId)
    {
        $payment = Payment::with(['ticket.ticketType.event', 'paymentMethod'])
            ->where('payment_id', $paymentId)
            ->firstOrFail();

        if (($payment->paymentMethod->name ?? '') !== 'Tiền mặt') {
            return redirect()->back()->with('error', 'Chỉ có thể từ chối thanh toán tiền mặt.');
        }

        $event = $payment->ticket->ticketType->event;
        $user = Auth::user();
        if (! $user->isAdmin() && $event->organizer_id !== $user->user_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối thanh toán này.');
        }

        if ($payment->status !== 'failed' || $payment->ticket->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Thanh toán này không thể từ chối.');
        }

        try {
            DB::beginTransaction();

            $ticket = $payment->ticket;
            $ticket->update([
                'payment_status' => 'cancelled',
            ]);

            // Tăng lại remaining_quantity cho ticket type
            $ticketType = $ticket->ticketType;
            $ticketType->increment('remaining_quantity', $ticket->quantity ?? 1);

            DB::commit();

            try {
                $this->notificationService->notifyPaymentRejected(
                    $ticket->attendee_id,
                    $event->title ?? $event->event_name,
                    $ticket->ticket_id
                );
            } catch (\Exception $e) {
                Log::error('Failed to send notification for payment rejection', [
                    'error' => $e->getMessage(),
                    'user_id' => $ticket->attendee_id,
                    'ticket_id' => $ticket->ticket_id,
                ]);
            }

            return redirect()->back()->with('success', 'Đã từ chối thanh toán. Vé đã được hủy và hoàn lại số lượng.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting cash payment: '.$e->getMessage());

            return redirect()->back()->with('error', 'Lỗi khi từ chối thanh toán: '.$e->getMessage());
        }
    }
}
