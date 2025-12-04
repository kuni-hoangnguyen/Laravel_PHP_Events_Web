@extends('layouts.app')

@section('title', $event->title . ' - Seniks Events')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <a href="{{ route('events.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-4">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Quay lại danh sách
        </a>

        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="relative overflow-hidden w-full h-64 md:h-96 bg-gray-200">
                @if ($event->banner_url)
                    <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-64 md:h-96 object-cover"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="hidden w-full h-64 md:h-96 bg-gray-300">
                        <div
                            class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer">
                        </div>
                    </div>
                @else
                    <div class="w-full h-64 md:h-96 bg-gray-300">
                        <div
                            class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer">
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                        {{ $event->category->name ?? 'N/A' }}
                    </span>
                    @if ($event->tags->count() > 0)
                        @foreach ($event->tags->take(3) as $tag)
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                #{{ $tag->name }}
                            </span>
                        @endforeach
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $event->start_time->format('d/m/Y H:i') }} -
                            {{ $event->end_time->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $event->location->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $event->organizer->full_name ?? ($event->organizer->name ?? 'N/A') }}</span>
                    </div>
                    @if ($event->max_attendees)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Tối đa {{ number_format($event->max_attendees) }} người</span>
                        </div>
                    @endif
                </div>

                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-4">
                    @php
                        $eventId = $event->event_id ?? $event->id;
                        $isOwner = auth()->check() && auth()->id() == $event->organizer_id;
                        $isAdmin = auth()->check() && auth()->user()->isAdmin();
                    @endphp
                    @auth

                        @if (auth()->user()->email_verified_at)
                            @if ($event->start_time > now())
                                <a href="{{ route('tickets.purchase', $eventId) }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
                                    Mua vé
                                </a>
                            @elseif($event->end_time < now())
                                @php
                                    $userId = auth()->id();
                                    $hasTicket = \App\Models\Ticket::where('attendee_id', $userId)
                                        ->whereHas('ticketType', function ($q) use ($eventId) {
                                            $q->where('event_id', $eventId);
                                        })
                                        ->where('payment_status', 'paid')
                                        ->exists();
                                    $eventEnded = $event->end_time < now();
                                @endphp
                                @if ($hasTicket && !$eventEnded)
                                    <a href="{{ route('reviews.create', $eventId) }}"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg">
                                        ⭐ Đánh giá sự kiện
                                    </a>
                                @endif
                            @endif
                        @else
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                                Vui lòng xác thực email để mua vé
                            </div>
                        @endif

                        <form method="POST" action="{{ route('favorites.toggle', $event->event_id ?? $event->id) }}"
                            class="inline">
                            @csrf
                            @php
                                $eventId = $event->event_id ?? $event->id;
                                $isFavorite = auth()->user()->favorites()->where('event_id', $eventId)->exists();
                            @endphp
                            <button type="submit"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg inline-flex items-center">
                                @if($isFavorite)
                                    <i class="fa-solid fa-heart mr-2"></i>Đã yêu thích
                                @else
                                    <i class="fa-regular fa-heart mr-2"></i>Yêu thích
                                @endif
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
                            Đăng nhập để mua vé
                        </a>
                    @endauth

                    @if ($isOwner || $isAdmin)
                        @if ($event->end_time >= now())
                            <a href="{{ route('events.checkin.scanner', $eventId) }}"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                QR Scanner
                            </a>
                            <a href="{{ route('events.checkin.stats', $eventId) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Thống kê
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Ticket Types -->
        @if ($event->ticketTypes->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Loại vé</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($event->ticketTypes as $ticketType)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $ticketType->name }}</h3>
                            <p class="text-2xl font-bold text-indigo-600 mb-2">{{ number_format($ticketType->price) }} đ
                            </p>
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($ticketType->description, 100) }}</p>
                            <p class="text-sm text-gray-500">Còn lại: {{ $ticketType->remaining_quantity }} /
                                {{ $ticketType->total_quantity }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Reviews Summary -->
        @if ($event->reviews->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Đánh giá</h2>
                @php
                    $avgRating = $event->reviews->avg('rating');
                @endphp
                <div class="flex items-center mb-4">
                    <span class="text-3xl font-bold text-gray-900 mr-2">{{ number_format($avgRating, 1) }}</span>
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= $avgRating ? 'text-yellow-400' : 'text-gray-300' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <span class="ml-2 text-gray-600">({{ $event->reviews->count() }} đánh giá)</span>
                </div>
                <a href="{{ route('events.reviews', $event->event_id ?? $event->id) }}"
                    class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    Xem tất cả đánh giá →
                </a>
            </div>
        @endif
    </div>
@endsection
