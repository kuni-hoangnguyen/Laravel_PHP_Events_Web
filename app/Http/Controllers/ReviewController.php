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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra user đã review event này chưa
        $existingReview = Review::where('event_id', $eventId)
                               ->where('user_id', Auth::id())
                               ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this event'
            ], 400);
        }

        $review = Review::create([
            'event_id' => $eventId,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review->load('user')
        ], 201);
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
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['rating', 'comment']));

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->load('user')
        ]);
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

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }
}