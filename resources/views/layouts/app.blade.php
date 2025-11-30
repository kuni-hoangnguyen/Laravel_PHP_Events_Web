<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Events Web - Quản lý sự kiện')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-calendar-alt me-2"></i>Events Web
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categories.index') }}">
                            <i class="fas fa-tags me-1"></i>Danh mục
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    @auth
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationCount">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <li>
                                    <h6 class="dropdown-header">Thông báo</h6>
                                </li>
                                <div id="notificationList">
                                    <li><span class="dropdown-item-text text-muted">Không có thông báo mới</span></li>
                                </div>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-center" href="{{ route('notifications.index') }}">Xem tất
                                        cả</a></li>
                            </ul>
                        </li>

                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->full_name ?? 'User' }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('tickets.my') }}">
                                        <i class="fas fa-ticket-alt me-2"></i>Vé của tôi
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('favorites.index') }}">
                                        <i class="fas fa-heart me-2"></i>Yêu thích
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('payments.my') }}">
                                        <i class="fas fa-credit-card me-2"></i>Thanh toán
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('auth.logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Events Web</h5>
                    <p class="text-muted">Nền tảng quản lý sự kiện hàng đầu Việt Nam</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2024 Events Web. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        // Load notifications
        @auth

        function loadNotifications() {
            fetch('/api/notifications/unread')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('notificationCount').textContent = data.data.total;

                        const notificationList = document.getElementById('notificationList');
                        if (data.data.notifications.length > 0) {
                            notificationList.innerHTML = data.data.notifications.map(notification =>
                                `<li><a class="dropdown-item" href="#" onclick="markAsRead(${notification.id})">
                                    <div class="fw-bold">${notification.title}</div>
                                    <small class="text-muted">${notification.message}</small>
                                </a></li>`
                            ).join('');
                        }
                    }
                })
                .catch(error => console.log('Error loading notifications:', error));
        }

        function markAsRead(notificationId) {
            fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            }).then(() => loadNotifications());
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', loadNotifications);

        // Refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);
        @endauth
    </script>

    @stack('scripts')
</body>

</html>
