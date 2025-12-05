@extends('layouts.app')

@section('title', 'Thông báo - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Thông báo</h1>
        <form method="POST" action="{{ route('notifications.mark.all.read') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                Đánh dấu tất cả đã đọc
            </button>
        </form>
    </div>

    @if($notifications->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    @php
                        $hasLink = !empty($notification->action_url);
                    @endphp
                    <div class="p-6 hover:bg-gray-50 {{ !$notification->is_read ? 'bg-blue-50' : '' }} {{ $hasLink ? 'cursor-pointer' : '' }}" 
                         @if($hasLink) onclick="window.location.href='{{ route('notifications.read.and.redirect', $notification->notification_id) }}'" @endif>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    @if(!$notification->is_read)
                                        <span class="w-2 h-2 bg-blue-600 rounded-full mr-2"></span>
                                    @endif
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        @if($hasLink)
                                            <a href="{{ route('notifications.read.and.redirect', $notification->notification_id) }}" class="hover:text-indigo-600">{{ $notification->title }}</a>
                                        @else
                                            {{ $notification->title }}
                                        @endif
                                    </h3>
                                </div>
                                <p class="text-gray-600 mb-2">{{ $notification->message }}</p>
                                <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                @if($hasLink)
                                    <p class="text-sm text-indigo-600 mt-2">
                                        <a href="{{ route('notifications.read.and.redirect', $notification->notification_id) }}" class="hover:underline">Xem chi tiết →</a>
                                    </p>
                                @endif
                            </div>
                            <div class="ml-4">
                                @if(!$notification->is_read)
                                    <form method="POST" action="{{ route('notifications.mark.read', $notification->notification_id) }}" class="inline" onclick="event.stopPropagation();">
                                        @csrf
                                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">
                                            Đánh dấu đã đọc
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Bạn chưa có thông báo nào.</p>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @auth
            const currentCount = {{ $unreadCount ?? 0 }};
            localStorage.setItem('last_notification_count', currentCount.toString());
        @endauth
    });
</script>
@endsection
