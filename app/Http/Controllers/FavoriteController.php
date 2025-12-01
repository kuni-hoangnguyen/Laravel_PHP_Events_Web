<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Favorite;
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
    public function store(int $eventId)
    {
        try {
            $userId = Auth::id();
            $event = Event::findOrFail($eventId);
            $existing = Favorite::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->first();
            if ($existing) {
                return redirect()->back()->with('warning', 'Sự kiện đã có trong danh sách yêu thích!');
            }
            Favorite::create([
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);

            return redirect()->back()->with('success', 'Đã thêm vào danh sách yêu thích!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi thêm yêu thích: '.$e->getMessage());
        }
    }

    /**
     * Xóa event khỏi danh sách yêu thích
     */
    public function destroy(int $eventId)
    {
        try {
            $userId = Auth::id();

            $favorite = Favorite::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->first();

            if (! $favorite) {
                return redirect()->back()->with('warning', 'Event không có trong danh sách yêu thích');
            }

            $favorite->delete();

            return redirect()->back()->with('success', 'Đã xóa event khỏi danh sách yêu thích');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa khỏi yêu thích: '.$e->getMessage());
        }
    }

    /**
     * Toggle favorite status (thêm/xóa)
     */
    public function toggle(int $eventId)
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
                return redirect()->back()->with('success', 'Đã xóa event khỏi danh sách yêu thích');
            } else {
                // Chưa favorite -> thêm
                Favorite::create([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                ]);
                return redirect()->back()->with('success', 'Đã thêm event vào danh sách yêu thích');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi cập nhật yêu thích: '.$e->getMessage());
        }
    }

    /**
     * Kiểm tra event có được user favorite không
     */
    public function check(int $eventId)
    {
        try {
            $userId = Auth::id();

            $isFavorited = Favorite::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->exists();

            if ($isFavorited) {
                return redirect()->back()->with('success', 'Event đã được yêu thích');
            } else {
                return redirect()->back()->with('warning', 'Event chưa được yêu thích');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi kiểm tra trạng thái yêu thích: '.$e->getMessage());
        }
    }

    /**
     * Lấy recommendations dựa trên favorites
     */
    public function recommendations()
    {
        try {
            $userId = Auth::id();
            $favoriteCategories = Event::whereHas('favorites', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->pluck('category_id')
                ->unique()
                ->toArray();
            if (empty($favoriteCategories)) {
                $recommendations = Event::where('approved', true)
                    ->where('status', 'upcoming')
                    ->withCount('favorites')
                    ->orderBy('favorites_count', 'desc')
                    ->with(['category', 'location', 'organizer'])
                    ->limit(6)
                    ->get();
            } else {
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
            return view('favorites.recommendations', compact('recommendations'))
                ->with('success', 'Lấy recommendations thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi lấy recommendations: '.$e->getMessage());
        }
    }
}
