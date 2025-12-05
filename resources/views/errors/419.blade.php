@extends('layouts.app')

@section('title', '419 - Phiên làm việc hết hạn')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-bold text-blue-600 mb-4">419</h1>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                Phiên làm việc hết hạn
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Phiên làm việc của bạn đã hết hạn. Vui lòng làm mới trang và thử lại.
            </p>
        </div>
        
        <div class="space-y-4">
            <button 
                onclick="window.location.reload()" 
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Làm mới trang
            </button>
            
<div>
                <a 
                    href="{{ route('home') }}" 
                    class="text-blue-600 hover:text-blue-800 font-medium"
                >
                    Về trang chủ
                </a>
            </div>
        </div>
        
        <div class="mt-12">
            <div class="text-gray-400 text-sm">
                <p>Lưu ý: Nếu bạn đang điền form, vui lòng sao chép dữ liệu trước khi làm mới trang.</p>
            </div>
        </div>
    </div>
</div>
@endsection
