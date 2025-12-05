@extends('layouts.app')

@section('title', 'QR Code Vé - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">QR Code Vé</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <div class="text-center mb-6">
            <img src="{{ $qrImageUrl }}" alt="QR Code" class="mx-auto w-80 h-80 border-4 border-gray-200 rounded-lg mb-4 shadow-sm" />
            <p class="text-lg font-semibold text-gray-900 mb-2">Mã QR: <span class="text-indigo-600">{{ $qrCode }}</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Sự kiện</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $ticket->ticketType->event->title ?? 'N/A' }}
                </p>
                </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Loại vé</p>
                <p class="text-lg font-semibold text-gray-900">{{ $ticket->ticketType->name ?? 'N/A' }}</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Người tham dự</p>
                <p class="text-lg font-semibold text-gray-900">{{ $ticket->attendee->full_name ?? 'N/A' }}</p>
        </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Trạng thái</p>
                @if($ticket->payment_status == 'paid')
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Đã thanh toán</span>
                @elseif($ticket->payment_status == 'used')
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Đã sử dụng</span>
                @else
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Chờ thanh toán</span>
    @endif
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Quay lại danh sách vé
            </a>
        </div>
    </div>
</div>
@endsection
