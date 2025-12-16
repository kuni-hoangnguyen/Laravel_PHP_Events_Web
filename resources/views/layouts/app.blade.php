<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Seniks Events - Nền tảng quản lý sự kiện')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-100 sticky top-0 z-50 backdrop-blur-sm bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('home') }}"
                            class="flex items-center gap-2 text-2xl font-bold text-indigo-600 hover:text-indigo-700 transition-all">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Seniks Events</span>
                        </a>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-6">
                        @php
                            $currentRoute = Route::currentRouteName();
                        @endphp
                        <a href="{{ route('home') }}"
                            class="{{ $currentRoute === 'home' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }} inline-flex items-center px-3 py-2 border-b-2 text-sm font-semibold transition-colors">
                            Trang chủ
                        </a>
                        <a href="{{ route('events.index') }}"
                            class="{{ str_starts_with($currentRoute, 'events.') && $currentRoute !== 'events.index' ? 'border-indigo-500 text-indigo-600' : ($currentRoute === 'events.index' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900') }} inline-flex items-center px-3 py-2 border-b-2 text-sm font-semibold transition-colors">
                            Sự kiện
                        </a>
                        @auth
                            <a href="{{ route('tickets.index') }}"
                                class="{{ str_starts_with($currentRoute, 'tickets.') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }} inline-flex items-center px-3 py-2 border-b-2 text-sm font-semibold transition-colors">
                                Vé của tôi
                            </a>
                            <a href="{{ route('favorites.index') }}"
                                class="{{ str_starts_with($currentRoute, 'favorites.') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }} inline-flex items-center px-3 py-2 border-b-2 text-sm font-semibold transition-colors">
                                Yêu thích
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    @auth
                        @php
                            $unreadNotificationCount = auth()
                                ->user()
                                ->notifications()
                                ->where('is_read', false)
                                ->count();
                        @endphp
                        <!-- Notification Icon -->
                        <a href="{{ route('notifications.index') }}" class="ml-3 relative">
                            <svg class="w-6 h-6 text-gray-600 hover:text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($unreadNotificationCount > 0)
                                <span
                                    class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                            @endif
                        </a>
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open"
                                    class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Mở menu người dùng</span>
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                            class="h-8 w-8 rounded-full object-cover border-2 border-indigo-500"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold"
                                            style="display: none;">
                                            {{ strtoupper(substr(auth()->user()->name ?? (auth()->user()->full_name ?? 'U'), 0, 1)) }}
                                        </div>
                                    @else
                                        <div
                                            class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr(auth()->user()->name ?? (auth()->user()->full_name ?? 'U'), 0, 1)) }}
                                        </div>
                                    @endif
                                </button>
                            </div>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <!-- Attendee Section -->
                                <div class="px-4 py-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Người tham dự
                                    </p>
                                </div>
                                <a href="{{ route('auth.me') }}"
                                    class="block px-4 py-2 text-sm {{ $currentRoute === 'auth.me' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">Hồ
                                    sơ</a>
                                <a href="{{ route('tickets.index') }}"
                                    class="block px-4 py-2 text-sm {{ str_starts_with($currentRoute, 'tickets.') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">Vé
                                    của tôi</a>
                                <a href="{{ route('payments.index') }}"
                                    class="block px-4 py-2 text-sm {{ str_starts_with($currentRoute, 'payments.') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">Thanh
                                    toán</a>
                                <a href="{{ route('favorites.index') }}"
                                    class="block px-4 py-2 text-sm {{ str_starts_with($currentRoute, 'favorites.') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">Yêu
                                    thích</a>
                                <a href="{{ route('notifications.index') }}"
                                    class="block px-4 py-2 text-sm {{ str_starts_with($currentRoute, 'notifications.') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                                    Thông báo
                                    @if ($unreadNotificationCount > 0)
                                        <span
                                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                    @endif
                                </a>

                                <!-- Organizer Section (hiển thị cho organizer và admin) -->
                                @if (auth()->user()->isOrganizer() || auth()->user()->isAdmin())
                                    <div class="border-t border-gray-200 mt-1"></div>
                                    <div class="px-4 py-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổ chức sự
                                            kiện</p>
                                    </div>
                                    @php
                                        $myEvents = \App\Models\Event::where('organizer_id', auth()->id())->get();
                                        $totalPendingPayments = 0;
                                        foreach ($myEvents as $event) {
                                            $totalPendingPayments += $event->pending_cash_payments_count;
                                        }
                                    @endphp
                                    <a href="{{ route('organizer.dashboard') }}"
                                        class="block px-4 py-2 text-sm {{ $currentRoute === 'organizer.dashboard' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">Dashboard</a>
                                    <a href="{{ route('events.my') }}"
                                        class="block px-4 py-2 text-sm {{ $currentRoute !== 'organizer.dashboard' && (in_array($currentRoute, ['events.my', 'events.create', 'events.edit', 'events.pending-payments']) || str_starts_with($currentRoute, 'events.checkin.')) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }} relative">
                                        Sự kiện của tôi
                                        @if ($totalPendingPayments > 0)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ $totalPendingPayments > 99 ? '99+' : $totalPendingPayments }}</span>
                                        @endif
                                    </a>
                                @endif

                                <!-- Admin Section (chỉ hiển thị cho admin, chỉ có Dashboard) -->
                                @if (auth()->user()->isAdmin())
                                    <div class="border-t border-gray-200 mt-1"></div>
                                    <div class="px-4 py-2">
                                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Quản trị
                                            hệ thống</p>
                                    </div>
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="block px-4 py-2 text-sm {{ str_starts_with($currentRoute, 'admin.') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-indigo-600 hover:bg-gray-100' }} font-semibold">Dashboard</a>
                                @endif

                                <!-- Logout -->
                                <div class="border-t border-gray-200 mt-1"></div>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Đăng
                                        xuất</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Đăng
                            nhập</a>
                        <a href="{{ route('register') }}"
                            class="ml-3 bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-2 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all">Đăng
                            ký</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Email Verification Banner -->
    @auth
        @if (!auth()->user()->email_verified_at)
            <div class="bg-yellow-50 border-b border-yellow-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <p class="text-sm text-yellow-800">
                                <strong>Email chưa được xác thực.</strong> Vui lòng kiểm tra email và xác thực để sử dụng
                                đầy đủ các tính năng.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('verification.send') }}" class="ml-4">
                            @csrf
                            <button type="submit"
                                class="text-sm text-yellow-800 hover:text-yellow-900 font-semibold underline">
                                Gửi lại email
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Toast Container -->
    <div x-data="toast()" x-init="init()" class="fixed bottom-4 right-4 z-50 space-y-2"
        style="max-width: 400px;">
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2 translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-y-0 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0 translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-y-2 translate-x-full" :class="toast.bgClass"
                class="rounded-lg shadow-lg p-4 flex items-start gap-3 min-w-[300px]" role="alert">
                <div :class="toast.iconClass" class="shrink-0 mt-0.5">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p :class="toast.textClass" class="text-sm font-medium" x-text="toast.message"></p>
                </div>
                <button @click="remove(index)" :class="toast.textClass" class="shrink-0 ml-2 hover:opacity-75">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>


    <!-- Main Content -->
    <main class="py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-2xl font-bold text-white">Seniks Events</span>
                    </div>
                    <p class="text-gray-400 mb-4 max-w-md">
                        Nền tảng quản lý sự kiện hàng đầu - Tìm kiếm, tham gia và tổ chức sự kiện một cách dễ dàng và
                        chuyên nghiệp.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Liên kết nhanh</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-400 transition-colors">Trang
                                chủ</a></li>
                        <li><a href="{{ route('events.index') }}" class="hover:text-indigo-400 transition-colors">Sự
                                kiện</a></li>
                        <li><a href="{{ route('categories.index') }}"
                                class="hover:text-indigo-400 transition-colors">Danh mục</a></li>
                        <li><a href="{{ route('locations.index') }}"
                                class="hover:text-indigo-400 transition-colors">Địa điểm</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Hỗ trợ</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Về chúng tôi</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Câu hỏi thường gặp</a>
                        </li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Liên hệ</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Điều khoản sử dụng</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">
                        &copy; {{ date('Y') }} Seniks Events. Tất cả quyền được bảo lưu.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.057-1.266-.07-1.646-.07-4.85 0-3.204.016-3.586.07-4.85.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.057 1.65-.07 4.859-.07zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162 0 3.403 2.759 6.162 6.162 6.162 3.403 0 6.162-2.759 6.162-6.162 0-3.403-2.759-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('toast', () => window.toastFunction());

            setTimeout(() => {
                    if (window.toastInstance) {
                        const flashKey = 'flash_shown_' + window.location.pathname;
                        const lastFlashTime = sessionStorage.getItem(flashKey);
                        const currentTime = Date.now();
                        const isNewPageLoad = !lastFlashTime || (currentTime - parseInt(lastFlashTime)) > 1000;

                        @php
                            $hasFlashMessage = session('success') || session('error') || session('warning') || session('info') || ($errors->any() ?? false);
                        @endphp

                        @if ($hasFlashMessage)
                            if (isNewPageLoad) {
                                @if (session('success'))
                                    window.toastInstance.show('{{ addslashes(session('success')) }}',
                                        'success');
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                                @if (session('error'))
                                    window.toastInstance.show('{{ addslashes(session('error')) }}', 'error');
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                                @if (session('warning'))
                                    window.toastInstance.show('{{ addslashes(session('warning')) }}',
                                        'warning');
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                                @if (session('info'))
                                    window.toastInstance.show('{{ addslashes(session('info')) }}', 'info');
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        setTimeout(() => window.toastInstance.show('{{ addslashes($error) }}',
                                            'error'), {{ $loop->index * 100 + 200 }});
                                    @endforeach
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                            }
                        @endif

                        @auth
                        @php
                            $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
                        @endphp
                        setTimeout(() => {
                            const currentCount = {{ $unreadCount }};
                            const lastKnownCount = localStorage.getItem('last_notification_count');

                            if (lastKnownCount !== null && currentCount > parseInt(lastKnownCount) &&
                                currentCount > 0) {
                                const newCount = currentCount - parseInt(lastKnownCount);
                                window.toastInstance.show('Bạn có ' + newCount + ' thông báo mới',
                                    'info', 6000);
                            }

                            if (lastKnownCount !== null) {
                                localStorage.setItem('last_notification_count', currentCount
                            .toString());
                            } else {
                                localStorage.setItem('last_notification_count', currentCount
                            .toString());
                            }
                        }, 800);
                    @endauth
                }
            }, 100);
        });
    </script>

    @stack('scripts')
</body>

</html>
