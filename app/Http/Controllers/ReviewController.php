<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends WelcomeController
{
    /**
     * Lấy danh sách reviews của event
     */
    public function index($eventId)
    {
        $event = Event::findOrFail($eventId);
        $reviews = Review::with(['user'])
                        ->where('event_id', $eventId)
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('events.reviews', compact('reviews', 'event'));
    }

    /**
     * Tạo review cho event (chỉ sau khi event kết thúc)
     */
    public function store(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu đánh giá không hợp lệ!');
        }
        // Kiểm tra user đã review event này chưa
        $existingReview = Review::where('event_id', $eventId)
                               ->where('user_id', Auth::id())
                               ->first();

        if ($existingReview) {
            return redirect()->back()->with('warning', 'Bạn đã đánh giá sự kiện này trước đó!');
        }
        try {
            $review = Review::create([
                'event_id' => $eventId,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
            return redirect()->back()->with('success', 'Đánh giá thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi đánh giá: ' . $e->getMessage());
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