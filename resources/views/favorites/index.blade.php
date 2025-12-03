@extends('layouts.app')

@section('title', 'Sự kiện yêu thích - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Sự kiện yêu thích</h1>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('favorites.index') }}" class="{{ !request()->has('tab') || request()->get('tab') == 'favorites' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Yêu thích
            </a>
            <a href="{{ route('favorites.index', ['tab' => 'recommendations']) }}" class="{{ request()->get('tab') == 'recommendations' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Gợi ý cho bạn
            </a>
        </nav>
    </div>

    @if(request()->get('tab') == 'recommendations')
        <!-- Recommendations Section -->
        @if(isset($recommendations) && $recommendations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @foreach($recommendations as $event)
                    @php $eventId = $event->event_id ?? $event->id; @endphp
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
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('events.show', $eventId) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Xem chi tiết
                                    </a>
                                    <form method="POST" action="{{ route('favorites.toggle', $eventId) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500 text-lg mb-4">Chưa có gợi ý sự kiện nào.</p>
                <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    Khám phá tất cả sự kiện →
                </a>
            </div>
        @endif
    @else
        <!-- Favorites Section -->
        @if($favorites->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($favorites as $favorite)
                @php 
                    $event = $favorite->event;
                    $eventId = $event->event_id ?? $event->id;
                @endphp
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
                        <div class="absolute top-4 right-4 flex gap-2">
                            @if($event->start_time <= now() && $event->end_time >= now())
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Đang diễn ra</span>
                            @else
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-semibold">Sắp diễn ra</span>
                            @endif
                            <form method="POST" action="{{ route('favorites.toggle', $eventId) }}" class="inline">
                                @csrf
                                <button type="submit" class="p-1 bg-white rounded-full hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </form>
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
                            <a href="{{ route('events.show', $eventId) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

            <div class="mt-6">
                {{ $favorites->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500 text-lg mb-4">Bạn chưa có sự kiện yêu thích nào.</p>
                <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    Khám phá sự kiện →
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
