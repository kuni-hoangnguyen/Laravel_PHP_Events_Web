@extends('layouts.app')

@section('title', 'Thống kê Check-in - ' . $event->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('events.my') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại sự kiện của tôi
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Thống kê Check-in: {{ $event->title }}</h1>

    @php
        $totalTickets = \App\Models\Ticket::whereHas('ticketType', function($q) use ($event) {
            $q->where('event_id', $event->event_id ?? $event->id);
        })->where('payment_status', 'paid')->sum('quantity');
        
        $checkedInTickets = \App\Models\Ticket::whereHas('ticketType', function($q) use ($event) {
            $q->where('event_id', $event->event_id ?? $event->id);
        })->where('payment_status', 'used')->sum('quantity');
        
        $pendingTickets = $totalTickets - $checkedInTickets;
        $checkInRate = $totalTickets > 0 ? ($checkedInTickets / $totalTickets) * 100 : 0;
    @endphp

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tổng số vé đã bán</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTickets) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Đã check-in</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($checkedInTickets) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Chưa check-in</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingTickets) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tỷ lệ check-in</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($checkInRate, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Tiến độ check-in</h2>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
            <div class="bg-indigo-600 h-4 rounded-full" style="width: {{ $checkInRate }}%"></div>
        </div>
        <p class="text-sm text-gray-600">{{ number_format($checkedInTickets) }} / {{ number_format($totalTickets) }} vé đã check-in</p>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
        <a href="{{ route('events.checkin.scanner', $event->event_id ?? $event->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
            Mở QR Scanner
        </a>
        <a href="{{ route('events.checkin.attendees', $event->event_id ?? $event->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg">
            Xem danh sách người tham gia
        </a>
    </div>
</div>
@endsection
