@extends('layouts.app')

@section('title', '500 - Lỗi máy chủ')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-orange-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-bold text-red-600 mb-4">500</h1>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                Lỗi máy chủ
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Xin lỗi, đã xảy ra lỗi trong quá trình xử lý yêu cầu của bạn. Chúng tôi đang khắc phục sự cố này.
            </p>
        </div>
        
        <div class="space-y-4">
            <a 
                href="{{ route('home') }}" 
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Về trang chủ
            </a>
            
            <div>
                <button 
                    onclick="window.history.back()" 
                    class="text-red-600 hover:text-red-800 font-medium"
                >
                    ← Quay lại trang trước
                </button>
            </div>
        </div>
        
        <div class="mt-12">
            <div class="text-gray-400 text-sm">
                <p>Vui lòng thử lại sau vài phút. Nếu vấn đề vẫn tiếp tục, hãy liên hệ với chúng tôi.</p>
            </div>
        </div>
    </div>
</div>
@endsection
