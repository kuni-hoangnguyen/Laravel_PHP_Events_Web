<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - Seniks Events')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Top Navigation Bar -->
    <nav class="bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-lg border-b border-indigo-500">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-2">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
<div>
                            <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                            <p class="text-xs text-indigo-200">Quản trị hệ thống</p>
                        </div>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->full_name ?? auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-200">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative flex items-center space-x-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg px-3 py-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/50">
                            <div class="h-9 w-9 rounded-full bg-white/30 flex items-center justify-center text-white font-semibold text-sm border-2 border-white/30">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
                            <div class="px-4 py-3 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->full_name ?? auth()->user()->name }}</p>
                                <p class="text-xs text-gray-600 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Dashboard
                                </a>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('home') }}" class="flex items-center px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Trang chủ
                                </a>
                            </div>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen shadow-sm">
            <nav class="mt-6 px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.events.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.events.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.events.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Quản lý sự kiện
                </a>
                
                <a href="{{ route('admin.users.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Quản lý người dùng
                </a>
                
                <a href="{{ route('admin.payments.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.payments.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.payments.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Quản lý thanh toán
                </a>
                
                <a href="{{ route('admin.tickets.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.tickets.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.tickets.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Quản lý vé
                </a>
                
                <a href="{{ route('admin.categories.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.categories.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Quản lý danh mục
                </a>
                
                <a href="{{ route('admin.locations.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.locations.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.locations.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Quản lý địa điểm
                </a>
                
                <a href="{{ route('admin.refunds.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.refunds.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.refunds.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Quản lý hoàn tiền
                </a>
                
                <a href="{{ route('admin.logs.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.logs.*') ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.logs.*') ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Nhật ký hệ thống
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50">
            <div class="py-8">
                <div class="max-w-7xl mx-auto px-6">
                    @yield('content')
                </div>
            </div>
        </main>
</div>

    <!-- Alpine.js for dropdown -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Toast Notification Script -->
    <script>
        function toast() {
            return {
                toasts: [],
                
                init() {
                    window.toastInstance = this;
                    this.initialized = false;
                    
                    // Show any pending flash messages after Alpine is ready
                    this.$nextTick(() => {
                        if (this.initialized) return;
                        this.initialized = true;
                        
                        // Use a unique key based on current URL and timestamp to ensure flash messages only show once per page load
                        const pageKey = window.location.pathname + '_' + Date.now();
                        const flashKey = 'flash_shown_' + window.location.pathname;
                        const lastFlashTime = sessionStorage.getItem(flashKey);
                        const currentTime = Date.now();
                        
                        // Only show flash if it's a new page load (not a back/forward navigation)
                        const isNewPageLoad = !lastFlashTime || (currentTime - parseInt(lastFlashTime)) > 1000;
                        
                        @php
                            $hasFlashMessage = session('success') || session('error') || session('warning') || session('info') || ($errors->any() ?? false);
                        @endphp
                        
                        @if($hasFlashMessage)
                            if (isNewPageLoad) {
                                @if(session('success'))
                                    setTimeout(() => {
                                        this.show('{{ addslashes(session('success')) }}', 'success');
                                        sessionStorage.setItem(flashKey, currentTime.toString());
                                    }, 100);
                                @endif
                                @if(session('error'))
                                    setTimeout(() => {
                                        this.show('{{ addslashes(session('error')) }}', 'error');
                                        sessionStorage.setItem(flashKey, currentTime.toString());
                                    }, 100);
                                @endif
                                @if(session('warning'))
                                    setTimeout(() => {
                                        this.show('{{ addslashes(session('warning')) }}', 'warning');
                                        sessionStorage.setItem(flashKey, currentTime.toString());
                                    }, 100);
                                @endif
                                @if(session('info'))
                                    setTimeout(() => {
                                        this.show('{{ addslashes(session('info')) }}', 'info');
                                        sessionStorage.setItem(flashKey, currentTime.toString());
                                    }, 100);
                                @endif
                                @if($errors->any())
                                    @foreach($errors->all() as $error)
                                        setTimeout(() => this.show('{{ addslashes($error) }}', 'error'), {{ $loop->index * 100 + 200 }});
                                    @endforeach
                                    sessionStorage.setItem(flashKey, currentTime.toString());
                                @endif
                            }
                        @endif
                    });
                },
                
                show(message, type = 'info', duration = 5000) {
                    const toastConfig = {
                        success: {
                            bgClass: 'bg-green-50 border border-green-200',
                            textClass: 'text-green-800',
                            iconClass: 'text-green-600'
                        },
                        error: {
                            bgClass: 'bg-red-50 border border-red-200',
                            textClass: 'text-red-800',
                            iconClass: 'text-red-600'
                        },
                        warning: {
                            bgClass: 'bg-yellow-50 border border-yellow-200',
                            textClass: 'text-yellow-800',
                            iconClass: 'text-yellow-600'
                        },
                        info: {
                            bgClass: 'bg-blue-50 border border-blue-200',
                            textClass: 'text-blue-800',
                            iconClass: 'text-blue-600'
                        }
                    };
                    
                    const config = toastConfig[type] || toastConfig.info;
                    
                    const toast = {
                        message: message,
                        type: type,
                        show: true,
                        ...config
                    };
                    
                    this.toasts.push(toast);
                    
                    // Auto remove after duration
                    setTimeout(() => {
                        this.remove(this.toasts.indexOf(toast));
                    }, duration);
                },
                
                remove(index) {
                    if (index > -1) {
                        this.toasts.splice(index, 1);
                    }
                }
            };
        }
    </script>

    <!-- Toast Container -->
    <div x-data="toast()" x-init="init()" class="fixed bottom-4 right-4 z-50 space-y-2" style="max-width: 400px;">
        <template x-for="(toast, index) in toasts" :key="index">
            <div 
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2 translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-y-0 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0 translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-y-2 translate-x-full"
                :class="toast.bgClass"
                class="rounded-lg shadow-lg p-4 flex items-start gap-3 min-w-[300px]"
                role="alert"
            >
                <div :class="toast.iconClass">
                    <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p :class="toast.textClass" class="text-sm font-medium" x-text="toast.message"></p>
                </div>
                <button @click="remove(index)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
</body>
</html>
