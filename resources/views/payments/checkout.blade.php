@extends('layouts.app')

@section('title', 'Thanh toán - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Thanh toán</h1>

    @php
        // This view can be used for payment confirmation/checkout
        // It might be called after ticket purchase
    @endphp

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <div class="text-center">
            <div class="mb-6">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Đang xử lý thanh toán...</h2>
                <p class="text-gray-600">Vui lòng chờ trong giây lát</p>
            </div>

            <div class="flex justify-center gap-4">
                <a href="{{ route('payments.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
                    Xem lịch sử thanh toán
                </a>
                <a href="{{ route('events.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg">
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
