<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Events Web')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body class="bg-light">
    <div class="min-vh-100 d-flex flex-column">
        <!-- Header -->
        <nav class="navbar navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">
                    <i class="fas fa-calendar-alt me-2"></i>Events Web
                </a>
                <div class="navbar-nav">
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i>Trang chủ
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow-1 d-flex align-items-center">
            <div class="container">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-dark text-white py-3">
            <div class="container text-center">
                <p class="mb-0">&copy; {{ date('Y') }} Events Web. Tất cả quyền được bảo lưu.</p>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
