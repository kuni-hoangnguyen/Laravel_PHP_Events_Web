@extends('layouts.app')

@section('title', 'Loại vé sự kiện - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại sự kiện
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Loại vé: {{ $event->title }}</h1>

    @if($ticketTypes && $ticketTypes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ticketTypes as $ticketType)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $ticketType->name }}</h3>
                    <p class="text-3xl font-bold text-indigo-600 mb-4">{{ number_format($ticketType->price) }} đ</p>
                    
                    @if($ticketType->description)
                        <p class="text-gray-600 text-sm mb-4">{{ $ticketType->description }}</p>
                    @endif

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tổng số lượng:</span>
                            <span class="font-semibold">{{ number_format($ticketType->total_quantity) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Còn lại:</span>
                            <span class="font-semibold text-green-600">{{ number_format($ticketType->remaining_quantity) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Bán từ:</span>
                            <span class="font-semibold">{{ $ticketType->sale_start_time->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Đến:</span>
                            <span class="font-semibold">{{ $ticketType->sale_end_time->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if($ticketType->active && $ticketType->remaining_quantity > 0)
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Đang bán</span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Không còn vé</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Sự kiện này chưa có loại vé nào.</p>
        </div>
    @endif
</div>
@endsection
