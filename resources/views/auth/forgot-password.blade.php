@extends('layouts.app')

@section('title', 'Quên mật khẩu - Seniks Events')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-6">Quên mật khẩu</h2>
        
        <p class="text-gray-600 text-center mb-6">
            Nhập email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu.
        </p>
        
        <form method="POST" action="{{ route('password.forgot') }}">
            @csrf
            
            <div class="mb-6">
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
                <button 
                    type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                >
                    Gửi link đặt lại mật khẩu
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Quay lại đăng nhập
            </a>
        </div>
    </div>
</div>
@endsection
