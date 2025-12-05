@extends('layouts.app')

@section('title', 'Đăng nhập - Seniks Events')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-6">Đăng nhập</h2>
        
        @if(session('warning'))
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-800 text-sm">{{ session('warning') }}</p>
                <p class="text-yellow-700 text-xs mt-2">Nếu bạn chưa nhận được email, vui lòng kiểm tra thư mục spam hoặc <a href="{{ route('register') }}" class="underline font-semibold">đăng ký lại</a>.</p>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                    placeholder="your@email.com"
                >
                @error('email')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                    Mật khẩu
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                    >
                    <span class="ml-2 text-sm text-gray-700">Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <div class="flex items-center justify-between mb-4">
                <button 
                    type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                >
                    Đăng nhập
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('password.forgot') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    Quên mật khẩu?
                </a>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Chưa có tài khoản? 
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    Đăng ký ngay
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
