@extends('layouts.app')

@section('title', 'Sự kiện đã kết thúc - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Sự kiện đã kết thúc</h1>
    <p class="text-gray-600 mb-6">Các sự kiện bạn đã tham gia và đã kết thúc. Hãy chia sẻ trải nghiệm của bạn!</p>

    @if($endedEvents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($endedEvents as $event)
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
                            <span class="px-3 py-1 bg-gray-600 text-white rounded-full text-xs font-semibold">Đã kết thúc</span>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col grow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-xs font-semibold">{{ $event->category->name ?? 'N/A' }}</span>
                            <span class="text-sm text-gray-500 font-medium">{{ $event->end_time->format('d/m/Y') }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">{{ $event->title }}</h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 grow">{{ Str::limit($event->description, 100) }}</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $event->end_time->format('H:i') }}
                            </div>
                            <div class="flex gap-2 mb-2">
                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Xem chi tiết
                                </a>
                                <a href="{{ route('events.reviews', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Xem đánh giá
                                </a>
                            </div>
                            @php
                                $hasReview = $event->reviews->count() > 0;
                                $canReview = !$hasReview && $event->end_time < now();
                            @endphp
                            @if($canReview)
                                <a href="{{ route('reviews.create', $event->event_id ?? $event->id) }}" class="block w-full text-center bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    ⭐ Đánh giá sự kiện
                                </a>
                            @elseif($hasReview)
                                <div class="mt-2 text-center">
                                    <span class="text-sm text-green-600 font-semibold">✓ Bạn đã đánh giá sự kiện này</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $endedEvents->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg mb-4">Bạn chưa tham gia sự kiện nào đã kết thúc.</p>
            <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                Khám phá sự kiện →
            </a>
        </div>
    @endif
</div>
@endsection
