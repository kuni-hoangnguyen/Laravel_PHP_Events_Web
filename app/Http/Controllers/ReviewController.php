<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Event;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewController extends WelcomeController
{
    /**
     * Lấy danh sách reviews của event
     */
    public function index($eventId)
    {
        $event = Event::with(['reviews.user'])->where('event_id', $eventId)->firstOrFail();
        $reviews = $event->reviews()->with('user')->latest()->paginate(10);

        return view('events.reviews', compact('reviews', 'event'));
    }

    /**
     * Hiển thị form tạo review
     */
    public function create($eventId)
    {
        $event = Event::where('event_id', $eventId)->firstOrFail();
        $userId = Auth::id();

        // Kiểm tra sự kiện đã kết thúc chưa
        if ($event->end_time >= now()) {
            return redirect()->route('events.show', $event->event_id)
                ->with('error', 'Chỉ có thể đánh giá sau khi sự kiện đã kết thúc.');
        }

        // Kiểm tra user đã mua vé chưa
        $hasTicket = \App\Models\Ticket::where('attendee_id', $userId)
            ->whereHas('ticketType', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasTicket) {
            return redirect()->route('events.show', $event->event_id)
                ->with('error', 'Bạn cần mua vé và thanh toán thành công để đánh giá sự kiện này.');
        }

        // Kiểm tra user đã review chưa
        $existingReview = Review::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReview) {
            return redirect()->route('events.reviews', $event->event_id)
                ->with('warning', 'Bạn đã đánh giá sự kiện này rồi. Bạn có thể chỉnh sửa đánh giá của mình.');
        }

        return view('reviews.create', compact('event'));
    }

    /**
     * Tạo review cho event (chỉ sau khi event kết thúc)
     */
    public function store(Request $request, $eventId)
    {
        $event = Event::where('event_id', $eventId)->firstOrFail();
        $userId = Auth::id();

        // Kiểm tra sự kiện đã kết thúc chưa
        if ($event->end_time >= now()) {
            return redirect()->route('events.show', $event->event_id)
                ->with('error', 'Chỉ có thể đánh giá sau khi sự kiện đã kết thúc.');
        }

        // Kiểm tra user đã mua vé chưa
        $hasTicket = \App\Models\Ticket::where('attendee_id', $userId)
            ->whereHas('ticketType', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasTicket) {
            return redirect()->route('events.show', $event->event_id)
                ->with('error', 'Bạn cần mua vé và thanh toán thành công để đánh giá sự kiện này.');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Kiểm tra user đã review event này chưa
        $existingReview = Review::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReview) {
            return redirect()->route('events.reviews', $event->event_id)
                ->with('warning', 'Bạn đã đánh giá sự kiện này trước đó!');
        }

        try {
            $review = Review::create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Log action
            try {
                AdminLog::logUserAction(null, 'create_review', 'reviews', $review->review_id, null, [
                    'event_id' => $eventId,
                    'rating' => $request->rating,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log create review action', ['error' => $e->getMessage()]);
            }

            return redirect()->route('events.reviews', $event->event_id)
                ->with('success', 'Đánh giá thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Lỗi khi đánh giá: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cập nhật review
     */
    public function update(Request $request, $reviewId)
    {
        $review = Review::where('review_id', $reviewId)
                       ->where('user_id', Auth::id())
                       ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu cập nhật đánh giá không hợp lệ!');
        }

        $review->update($request->only(['rating', 'comment']));

        return redirect()->back()->with('success', 'Cập nhật đánh giá thành công!');
    }

    /**
     * Xóa review
     */
    public function destroy($reviewId)
    {
        $review = Review::where('review_id', $reviewId)
                       ->where('user_id', Auth::id())
                       ->firstOrFail();

        $review->delete();

        return redirect()->back()->with('success', 'Xóa đánh giá thành công!');
    }
}