@extends('layouts.app')

@section('title', 'Events Web - Nền tảng quản lý sự kiện hàng đầu')

@section('content')
    <div class="container">
        <div class="mb-4">
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Đăng xuất</button>
            </form>
        </div>

        <h1>Welcome to Events Web!</h1>
        
        <div style="margin: 30px 0;">
            <h2>📊 Stats Data:</h2>
            <pre>{{ json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <div style="margin: 30px 0;">
            <h2>🌟 Featured Events Data:</h2>
            <pre>{{ json_encode($featuredEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <div style="margin: 30px 0;">
            <h2>📂 Categories Data:</h2>
            <pre>{{ json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <hr style="margin: 40px 0;">

        <h1 style="margin-bottom: 30px;">🔗 All Available Routes</h1>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #28a745;">🌐 PUBLIC ROUTES (No Authentication Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('home') }}">{{ route('home') }}</a> - Home/Welcome</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('register') }}">{{ route('register') }}</a> - Register Form</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('auth.register') }} - Register</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('login') }}">{{ route('login') }}</a> - Login Form</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('auth.login') }} - Login</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('auth.show-forgot-password') }}">{{ route('auth.show-forgot-password') }}</a> - Forgot Password Form</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('auth.forgot-password') }} - Forgot Password</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('events.index') }}">{{ route('events.index') }}</a> - Events List</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('events.show', 1) }}">{{ route('events.show', 1) }}</a> - Event Details (example: /events/1)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('events.reviews', 1) }}">{{ route('events.reviews', 1) }}</a> - Event Reviews (example: /events/1/reviews)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('events.ticket-types', 1) }} - Event Ticket Types (example: /events/1/ticket-types)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('categories.index') }} - Categories List</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('locations.index') }} - Locations List</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #007bff;">🔐 AUTHENTICATED ROUTES (Login Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('auth.logout') }} - Logout</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('auth.me') }} - Current User Info</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('auth.verify-email') }} - Verify Email</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('tickets.my') }}">{{ route('tickets.my') }}</a> - My Tickets</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('payments.my') }}">{{ route('payments.my') }}</a> - My Payments</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('payments.show', 1) }}">{{ route('payments.show', 1) }}</a> - Payment Details (example: /payments/1)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('notifications.index') }}">{{ route('notifications.index') }}</a> - Notifications</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('notifications.unread.count') }} - Unread Count</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('notifications.mark.read', 1) }} - Mark Notification as Read (example: /notifications/1/read)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('notifications.mark.all.read') }} - Mark All as Read</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('favorites.index') }}">{{ route('favorites.index') }}</a> - My Favorites</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('favorites.recommendations') }} - Recommendations</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('favorites.store', 1) }} - Add to Favorites (example: /favorites/events/1)</li>
                <li style="margin: 10px 0;"><strong>DELETE</strong> {{ route('favorites.destroy', 1) }} - Remove from Favorites (example: /favorites/events/1)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('favorites.toggle', 1) }} - Toggle Favorite (example: /favorites/events/1/toggle)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('favorites.check', 1) }} - Check Favorite Status (example: /favorites/events/1/check)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #20c997;">🚀 QR CODE ROUTES (Login & Event Owner Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('ticket.qr', 1) }}">{{ route('ticket.qr', 1) }}</a> - Lấy QR code cho vé (example: /ticket/1/qr)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('event.checkin.stats', 1) }}">{{ route('event.checkin.stats', 1) }}</a> - Thống kê check-in sự kiện (example: /event/1/checkin-stats)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('event.attendees', 1) }}">{{ route('event.attendees', 1) }}</a> - Danh sách attendees đã check-in (example: /event/1/attendees)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('event.qr.scanner', 1) }}">{{ route('event.qr.scanner', 1) }}</a> - Mở trình quét QR code (example: /event/1/qr-scanner)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('event.qr.checkin', 1) }} - Check-in sự kiện bằng QR code (example: /event/1/checkin)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #ff9800;">💖 FAVORITE ROUTES (Yêu thích - Đăng nhập)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('favorites.index') }}">{{ route('favorites.index') }}</a> - Danh sách sự kiện yêu thích</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('favorites.recommendations') }} - Gợi ý sự kiện yêu thích</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('favorites.store', 1) }} - Thêm sự kiện vào yêu thích (example: /favorites/events/1)</li>
                <li style="margin: 10px 0;"><strong>DELETE</strong> {{ route('favorites.destroy', 1) }} - Xóa sự kiện khỏi yêu thích (example: /favorites/events/1)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('favorites.toggle', 1) }} - Chuyển trạng thái yêu thích (example: /favorites/events/1/toggle)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> {{ route('favorites.check', 1) }} - Kiểm tra trạng thái yêu thích (example: /favorites/events/1/check)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #ffc107;">✅ VERIFIED EMAIL ROUTES (Email Verification Required)</h2>
            <ul style="list-style: none; padding: 0">
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('tickets.purchase', 1) }} - Purchase Tickets (example: /events/1/tickets)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('reviews.store', 1) }} - Create Review (example: /events/1/reviews)</li>
                <li style="margin: 10px 0;"><strong>PUT</strong> {{ route('reviews.update', 1) }} - Update Review (example: /reviews/1)</li>
                <li style="margin: 10px 0;"><strong>DELETE</strong> {{ route('reviews.destroy', 1) }} - Delete Review (example: /reviews/1)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #17a2b8;">🎯 ORGANIZER ROUTES (Organizer Role Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('events.store') }} - Create Event</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('events.my') }}">{{ route('events.my') }}</a> - My Events</li>
                <li style="margin: 10px 0;"><strong>PUT</strong> {{ route('events.update', 1) }} - Update Event (example: /my-events/1)</li>
                <li style="margin: 10px 0;"><strong>DELETE</strong> {{ route('events.destroy', 1) }} - Delete Event (example: /my-events/1)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #dc3545;">🎫 TICKET OWNER ROUTES (Ticket Owner Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('tickets.show', 1) }}">{{ route('tickets.show', 1) }}</a> - Ticket Details (example: /my-tickets/1)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #6f42c1;">💳 PAYMENT VERIFICATION ROUTES (Payment Owner Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('payments.confirm', 1) }} - Confirm Payment (example: /payments/1/confirm)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('payments.refund', 1) }} - Request Refund (example: /payments/1/refund)</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #e83e8c;">👑 ADMIN ROUTES (Admin Role Required)</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('admin.dashboard') }}">{{ route('admin.dashboard') }}</a> - Admin Dashboard</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('admin.events.index') }}">{{ route('admin.events.index') }}</a> - Admin Events Management</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('admin.events.approve', 1) }} - Approve Event (example: /admin/events/1/approve)</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('admin.events.reject', 1) }} - Reject Event (example: /admin/events/1/reject)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('admin.users.index') }}">{{ route('admin.users.index') }}</a> - Admin Users Management</li>
                <li style="margin: 10px 0;"><strong>PUT</strong> {{ route('admin.users.role.update', 1) }} - Update User Role (example: /admin/users/1/role)</li>
                <li style="margin: 10px 0;"><strong>DELETE</strong> {{ route('admin.users.destroy', 1) }} - Delete User (example: /admin/users/1)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('admin.refunds.index') }}">{{ route('admin.refunds.index') }}</a> - Admin Refunds Management</li>
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('admin.refunds.process', 1) }} - Process Refund (example: /admin/refunds/1/process)</li>
                <li style="margin: 10px 0;"><strong>GET</strong> <a href="{{ route('admin.logs.index') }}">{{ route('admin.logs.index') }}</a> - Admin Logs</li>
            </ul>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #fd7e14;">🔧 SPECIAL ROUTES</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="margin: 10px 0;"><strong>POST</strong> {{ route('tickets.check-in', 1) }} - Ticket Check-in (example: /tickets/1/check-in)</li>
            </ul>
        </div>

        <style>
            a {
                color: #007bff;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
            ul li {
                padding: 5px;
                background: #f8f9fa;
                border-left: 3px solid #007bff;
                padding-left: 10px;
            }
        </style>
    </div>
@endsection
