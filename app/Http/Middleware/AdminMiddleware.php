<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
use App\Models\AdminLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('home')->with('error', 'Bạn cần đăng nhập để truy cập trang này.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isAdmin()) {
            return redirect()->route('home')->with('error', 'Bạn cần có vai trò Admin để truy cập trang này.');
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $action = $this->getActionFromRequest($request);
            if ($action !== 'skip_logging') {
                $this->logAdminAction($request, $user, $action);
            }
        }

        return $next($request);
    }

    /**
     * Log admin actions để audit
     */
    private function logAdminAction(Request $request, $user, $action = null)
    {
        if ($action === null) {
            $action = $this->getActionFromRequest($request);
        }

        AdminLog::logAction(
            adminId: $user->user_id,
            action: $action,
            targetTable: $this->getTargetTable($request),
            targetId: $this->getTargetId($request),
            oldValues: null,
            newValues: LogHelper::sanitizeForAdminLog($request->all())
        );
    }

    /**
     * Lấy action từ request
     */
    private function getActionFromRequest(Request $request): string
    {
        $method = $request->method();
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $path = $request->path();
        
        $routesWithManualLogging = [
            'admin.events.approve',
            'admin.events.reject',
            'admin.events.approve-cancellation',
            'admin.events.reject-cancellation',
            'admin.events.delete',
            'admin.users.role.update',
            'admin.users.destroy',
            'admin.refunds.process',
            'admin.categories.store',
            'admin.categories.update',
            'admin.categories.destroy',
            'admin.locations.store',
            'admin.locations.update',
            'admin.locations.destroy',
        ];
        
        if (in_array($routeName, $routesWithManualLogging)) {
            return 'skip_logging';
        }
        
        if (str_contains($routeName, 'approve-cancellation') || str_contains($path, 'approve-cancellation')) {
            return 'approve_cancellation';
        }
        if (str_contains($routeName, 'reject-cancellation') || str_contains($path, 'reject-cancellation')) {
            return 'reject_cancellation';
        }
        if (str_contains($routeName, 'approve')) {
            return 'approve_event';
        }
        if (str_contains($routeName, 'reject')) {
            return 'reject_event';
        }
        if (str_contains($routeName, 'ban')) {
            return 'ban_user';
        }
        if (str_contains($routeName, 'unban')) {
            return 'unban_user';
        }
        if (str_contains($routeName, 'refund')) {
            return 'process_refund';
        }

        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'unknown_action'
        };
    }

    /**
     * Lấy target table từ request path
     */
    private function getTargetTable(Request $request): ?string
    {
        $path = $request->path();

        if (str_contains($path, 'events')) {
            return 'events';
        }
        if (str_contains($path, 'users')) {
            return 'users';
        }
        if (str_contains($path, 'tickets')) {
            return 'tickets';
        }
        if (str_contains($path, 'payments')) {
            return 'payments';
        }
        if (str_contains($path, 'refunds')) {
            return 'refunds';
        }

        return null;
    }

    /**
     * Lấy target ID từ route parameters
     */
    private function getTargetId(Request $request): ?int
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        foreach (['id', 'event', 'user', 'ticket', 'payment'] as $param) {
            if ($route->hasParameter($param)) {
                return (int) $route->parameter($param);
            }
        }

        return null;
    }
}