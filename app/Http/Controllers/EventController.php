<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventLocation;
use App\Http\Requests\StoreEventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends WelcomeController
{
    /**
     * Lấy danh sách events với filtering và pagination
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'location', 'organizer'])
                     ->where('approved', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_time', '<=', $request->end_date);
        }

        $events = $query->paginate(12);

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
            'tags'
        ])->findOrFail($id);

        return view('events.show', compact('event'));
    }

    /**
     * Tạo event mới (Organizer only)
     */
    public function store(StoreEventRequest $request)
    {
        // Validation đã được thực hiện tự động bởi StoreEventRequest
        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'category_id' => $request->category_id,
            'location_id' => $request->location_id,
            'organizer_id' => Auth::id(),
            'max_attendees' => $request->max_attendees,
            'banner_url' => $request->banner_url,
            'approved' => false, // Cần admin approve
        ]);

        return redirect()->back()->with('success', 'Tạo sự kiện thành công!');
    }

    /**
     * Cập nhật event (Owner only)
     */
    public function update(Request $request, $id)
    {
        $event = $request->event; // Từ middleware event.owner

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'description' => 'sometimes|string',
            'start_time' => 'sometimes|date|after:now',
            'end_time' => 'sometimes|date|after:start_time',
            'category_id' => 'sometimes|exists:event_categories,category_id',
            'location_id' => 'sometimes|exists:event_locations,location_id',
            'max_attendees' => 'sometimes|integer|min:1',
            'banner_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Dữ liệu cập nhật sự kiện không hợp lệ!');
        }

        $event->update($request->only([
            'title', 'description', 'start_time', 'end_time',
            'category_id', 'location_id', 'max_attendees', 'banner_url'
        ]));

        return redirect()->back()->with('success', 'Cập nhật sự kiện thành công!');
    }

    /**
     * Xóa event (Owner only)
     */
    public function destroy(Request $request, $id)
    {
        $event = $request->event; // Từ middleware event.owner
        
        $event->delete();

        return redirect()->back()->with('success', 'Xóa sự kiện thành công!');
    }

    /**
     * Lấy events do user tổ chức
     */
    public function myEvents()
    {
        $events = Event::with(['category', 'location'])
                      ->where('organizer_id', Auth::id())
                      ->paginate(12);

        return view('events.my', compact('events'));
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
}