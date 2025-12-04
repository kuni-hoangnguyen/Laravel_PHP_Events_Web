@extends('layouts.app')

@section('title', 'Seniks Events - Nền tảng quản lý sự kiện hàng đầu')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl overflow-hidden mb-12">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative p-8 md:p-16 lg:p-20 text-white">
            <div class="max-w-4xl">
                <div class="inline-block mb-4 px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full text-sm font-semibold text-indigo-600 " >
                    🎉 Nền tảng quản lý sự kiện số 1
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Khám phá & Tổ chức<br>
                    <span class="text-yellow-300">Sự kiện tuyệt vời</span>
                </h1>
                <p class="text-lg md:text-xl mb-8 text-gray-100 leading-relaxed max-w-2xl">
                    Tìm kiếm, tham gia và tổ chức các sự kiện một cách dễ dàng. Hệ thống quản lý chuyên nghiệp với thanh toán an toàn và công cụ mạnh mẽ.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('events.index') }}" class="group bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                        <span class="flex items-center justify-center gap-2">
                            Khám phá sự kiện
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="bg-indigo-800 bg-opacity-80 backdrop-blur-sm text-white px-8 py-4 rounded-xl font-bold hover:bg-opacity-100 transition-all duration-300 transform hover:scale-105 shadow-lg text-center border-2 border-white border-opacity-30">
                        Đăng ký ngay
                    </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Events -->
    <div class="mb-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Sự kiện nổi bật</h2>
                <p class="text-gray-600">Khám phá những sự kiện đang được quan tâm nhất</p>
            </div>
            <a href="{{ route('events.index') }}" class="hidden md:flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                Xem tất cả
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
        @php
            $featuredEvents = \App\Models\Event::with(['category', 'location', 'organizer'])
                ->where('approved', 1)
                ->where('status', '!=', 'cancelled')
                ->where('end_time', '>=', now()) // Hiển thị sự kiện sắp diễn ra và đang diễn ra
                ->orderBy('start_time', 'asc')
                ->take(6)
                ->get();
        @endphp

        @if($featuredEvents->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredEvents as $event)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                        <div class="relative overflow-hidden w-full h-48 bg-gray-200">
                            @if($event->banner_url)
                                <img 
                                    src="{{ $event->banner_url }}" 
                                    alt="{{ $event->title }}" 
                                    class="w-full h-48 object-cover transition-transform duration-300 hover:scale-110"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                >
                                <div class="hidden w-full h-48 bg-gray-300">
                                    <div class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer"></div>
                                </div>
                            @else
                                <div class="w-full h-48 bg-gray-300">
                                    <div class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer"></div>
                                </div>
                            @endif
                            <div class="absolute top-4 right-4">
                                @if($event->start_time <= now() && $event->end_time >= now())
                                    <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Đang diễn ra</span>
                                @else
                                    <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-semibold">Sắp diễn ra</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-6 flex flex-col grow">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-xs font-semibold">{{ $event->category->name ?? 'N/A' }}</span>
                                <span class="text-sm text-gray-500 font-medium">{{ $event->start_time->format('d/m/Y') }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">{{ $event->title }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 grow">{{ Str::limit($event->description, 100) }}</p>
                            <div class="mt-auto">
                                <div class="flex items-center text-sm text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="truncate">{{ $event->location->name ?? 'N/A' }}</span>
                                </div>
                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 text-center md:hidden">
                <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                    Xem tất cả sự kiện
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="max-w-md mx-auto">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg mb-4">Chưa có sự kiện nổi bật nào.</p>
                    <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                        Xem tất cả sự kiện
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Features Section -->
    <div class="mb-12">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Tại sao chọn Seniks Events?</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Giải pháp toàn diện cho mọi nhu cầu tổ chức và tham gia sự kiện</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow-lg p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl w-20 h-20 flex items-center justify-center mx-auto mb-6 shadow-lg transform hover:scale-110 transition-transform duration-300">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Dễ dàng tìm kiếm</h3>
                <p class="text-gray-600 leading-relaxed">Tìm kiếm sự kiện theo danh mục, địa điểm và thời gian một cách nhanh chóng với bộ lọc thông minh</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl w-20 h-20 flex items-center justify-center mx-auto mb-6 shadow-lg transform hover:scale-110 transition-transform duration-300">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Thanh toán an toàn</h3>
                <p class="text-gray-600 leading-relaxed">Hệ thống thanh toán an toàn và bảo mật, hỗ trợ nhiều phương thức thanh toán linh hoạt</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl w-20 h-20 flex items-center justify-center mx-auto mb-6 shadow-lg transform hover:scale-110 transition-transform duration-300">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Quản lý chuyên nghiệp</h3>
                <p class="text-gray-600 leading-relaxed">Tổ chức sự kiện và quản lý vé một cách chuyên nghiệp với công cụ mạnh mẽ và dễ sử dụng</p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    @guest
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-2xl p-8 md:p-12 text-center text-white mb-12">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Sẵn sàng bắt đầu?</h2>
        <p class="text-xl text-gray-100 mb-8 max-w-2xl mx-auto">Tham gia ngay để khám phá và tổ chức những sự kiện tuyệt vời</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                Đăng ký miễn phí
            </a>
            <a href="{{ route('events.index') }}" class="bg-indigo-800 bg-opacity-80 backdrop-blur-sm text-white px-8 py-4 rounded-xl font-bold hover:bg-opacity-100 transition-all duration-300 transform hover:scale-105 shadow-lg border-2 border-white border-opacity-30">
                Khám phá sự kiện
            </a>
        </div>
    </div>
    @endguest
</div>
@endsection
