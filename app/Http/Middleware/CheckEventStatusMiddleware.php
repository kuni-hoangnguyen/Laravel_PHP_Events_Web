<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Event;

class CheckEventStatusMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware này kiểm tra status của event trước khi cho phép các hành động
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy event ID từ request
        $eventId = $this->getEventIdFromRequest($request);
        
        if (!$eventId) {
            return redirect()->back()->with('error', 'Không tìm thấy Event ID trong request.');
        }

        // Tìm event
        $event = Event::find($eventId);
        
        if (!$event) {
            return redirect()->back()->with('error', 'Không tìm thấy sự kiện.');
        }

        // Kiểm tra các trường hợp không được phép
        $action = $this->getActionFromRequest($request);
        
        switch ($action) {
            case 'buy_ticket':
                if (!$this->canBuyTicket($event)) {
                    return redirect()->back()->with('warning', 'Không thể mua vé cho sự kiện này. Sự kiện có thể đã bị hủy, kết thúc hoặc chưa được duyệt.');
                }
                break;
                
            case 'review':
                if (!$this->canReview($event)) {
                    return redirect()->back()->with('warning', 'Không thể đánh giá sự kiện này. Chỉ đánh giá khi sự kiện đã kết thúc.');
                }
                break;
                
            case 'edit':
                if (!$this->canEdit($event)) {
                    return redirect()->back()->with('warning', 'Không thể chỉnh sửa sự kiện này. Sự kiện đã bắt đầu hoặc kết thúc.');
                }
                break;
        }

        // Gắn event vào request để controller không phải query lại
        $request->merge(['event' => $event]);

        return $next($request);
    }

    /**
     * Kiểm tra có thể mua vé không
     */
    private function canBuyTicket(Event $event): bool
    {
        // Kiểm tra: approved = 1 (đã duyệt), status không phải cancelled, chưa bắt đầu, và không có yêu cầu hủy đang chờ
        return $event->approved == 1 
            && $event->status != 'cancelled'
            && !$event->cancellation_requested
            && $event->start_time > now();
    }

    /**
     * Kiểm tra có thể review không
     */
    private function canReview(Event $event): bool
    {
        return $event->status === 'ended' 
            && $event->end_time < now();
    }

    /**
     * Kiểm tra có thể edit không
     */
    private function canEdit(Event $event): bool
    {
        return in_array($event->status, ['upcoming']) 
            && $event->start_time > now();
    }

    /**
     * Lấy action từ request
     */
    private function getActionFromRequest(Request $request): string
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $path = $request->path();
        
        if (str_contains($routeName, 'ticket') || str_contains($path, 'ticket')) {
            return 'buy_ticket';
        }
        
        if (str_contains($routeName, 'review') || str_contains($path, 'review')) {
            return 'review';
        }
        
        if (in_array($request->method(), ['PUT', 'PATCH'])) {
            return 'edit';
        }
        
        return 'unknown';
    }

    /**
     * Lấy Event ID từ request
     */
    private function getEventIdFromRequest(Request $request): ?int
    {
        $route = $request->route();
        if (!$route) return null;

        // Thử lấy từ các parameter có thể có
        if ($route->hasParameter('event')) {
            return (int) $route->parameter('event');
        }
        
        if ($route->hasParameter('id')) {
            return (int) $route->parameter('id');
        }
        
        if ($request->has('event_id')) {
            return (int) $request->get('event_id');
        }

        return null;
    }
}
