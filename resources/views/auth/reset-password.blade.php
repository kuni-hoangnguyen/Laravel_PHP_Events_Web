@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - Seniks Events')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-6">Đặt lại mật khẩu</h2>

            <p class="text-gray-600 text-center mb-6">
                Vui lòng nhập mật khẩu mới cho tài khoản của bạn.
            </p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                        Email
                    </label>
                    <input type="email" id="email" name="email_display" value="{{ $email }}" disabled
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                        Mật khẩu mới
                    </label>
                    <input type="password" id="password" name="password" required minlength="8" autofocus
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Mật khẩu phải có ít nhất 8 ký tự</p>
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                        Xác nhận mật khẩu mới
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password_confirmation') border-red-500 @enderror"
                        placeholder="••••••••">
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                        Đặt lại mật khẩu
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
