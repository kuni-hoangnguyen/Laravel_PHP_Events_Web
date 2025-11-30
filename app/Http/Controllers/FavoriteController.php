<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý tính năng yêu thích events
 */
class FavoriteController extends WelcomeController
{
    /**
     * Lấy danh sách events yêu thích của user
     */
    public function index()
    {
        $userId = Auth::id();
        
        $favorites = Event::whereHas('favorites', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['category', 'location', 'organizer'])
        ->orderBy('favorites.created_at', 'desc')
        ->paginate(12);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Thêm event vào danh sách yêu thích
     */
    public function store(int $eventId): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Kiểm tra event có tồn tại không
            $event = Event::findOrFail($eventId);

            // Kiểm tra đã favorite chưa
            $existing = Favorite::where('user_id', $userId)
                              ->where('event_id', $eventId)
                              ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event đã có trong danh sách yêu thích'
                ], 409);
            }

            // Thêm vào favorites
            Favorite::create([
                'user_id' => $userId,
                'event_id' => $eventId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm event vào danh sách yêu thích'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa event khỏi danh sách yêu thích
     */
    public function destroy(int $eventId): JsonResponse
    {
        try {
            $userId = Auth::id();

            $favorite = Favorite::where('user_id', $userId)
                              ->where('event_id', $eventId)
                              ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event không có trong danh sách yêu thích'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa event khỏi danh sách yêu thích'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa khỏi yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite status (thêm/xóa)
     */
    public function toggle(int $eventId): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Kiểm tra event có tồn tại không
            $event = Event::findOrFail($eventId);

            $favorite = Favorite::where('user_id', $userId)
                              ->where('event_id', $eventId)
                              ->first();

            if ($favorite) {
                // Đã favorite -> xóa
                $favorite->delete();
                $message = 'Đã xóa event khỏi danh sách yêu thích';
                $isFavorited = false;
            } else {
                // Chưa favorite -> thêm
                Favorite::create([
                    'user_id' => $userId,
                    'event_id' => $eventId
                ]);
                $message = 'Đã thêm event vào danh sách yêu thích';
                $isFavorited = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_favorited' => $isFavorited
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra event có được user favorite không
     */
    public function check(int $eventId): JsonResponse
    {
        try {
            $userId = Auth::id();

            $isFavorited = Favorite::where('user_id', $userId)
                                 ->where('event_id', $eventId)
                                 ->exists();

            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra trạng thái yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy recommendations dựa trên favorites
     */
    public function recommendations(): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Lấy categories từ events đã favorite
            $favoriteCategories = Event::whereHas('favorites', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->pluck('category_id')
            ->unique()
            ->toArray();

            if (empty($favoriteCategories)) {
                // Nếu chưa có favorite nào, recommend events phổ biến
                $recommendations = Event::where('approved', true)
                    ->where('status', 'upcoming')
                    ->withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->with(['category', 'location', 'organizer'])
                    ->limit(6)
                    ->get();
            } else {
                // Recommend events cùng category
                $recommendations = Event::where('approved', true)
                    ->where('status', 'upcoming')
                    ->whereIn('category_id', $favoriteCategories)
                    ->whereDoesntHave('favorites', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->with(['category', 'location', 'organizer'])
                    ->limit(6)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy recommendations: ' . $e->getMessage()
            ], 500);
        }
    }
}