@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Hồ sơ cá nhân</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <form method="POST" action="{{ route('auth.me') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    value="{{ old('full_name', auth()->user()->full_name) }}"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    value="{{ auth()->user()->email }}"
                    disabled
                    class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100"
                >
                <p class="mt-1 text-sm text-gray-500">Email không thể thay đổi</p>
            </div>

            <div class="mb-6">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    value="{{ old('phone', auth()->user()->phone) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="mb-6">
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Ảnh đại diện</label>
                @if(auth()->user()->avatar_url)
                    <div class="mb-3">
                        <p class="text-sm text-gray-600 mb-2">Ảnh hiện tại:</p>
                        <img src="{{ auth()->user()->avatar_url }}" alt="Avatar hiện tại" class="w-32 h-32 object-cover rounded-full border-2 border-gray-300">
                    </div>
                @endif
                <input 
                    type="file" 
                    id="avatar" 
                    name="avatar" 
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('avatar') border-red-500 @enderror"
                    onchange="window.previewImage(this, 'avatar-preview')"
                >
                <p class="mt-1 text-sm text-gray-500">Chấp nhận: JPEG, PNG, GIF, WebP. Kích thước tối đa: 5MB</p>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div id="avatar-preview" class="mt-4 hidden">
                    <p class="text-sm text-gray-600 mb-2">Ảnh mới:</p>
                    <img id="avatar-preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-full border-2 border-gray-300">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái email</label>
                @if(auth()->user()->email_verified_at)
                    <div class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Email đã được xác thực
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-yellow-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Email chưa được xác thực
                        </div>
                        <button 
                            type="button"
                            onclick="document.getElementById('resend-verification-form').submit();"
                            class="text-indigo-600 hover:text-indigo-800 font-semibold"
                        >
                            Gửi lại email xác thực
                        </button>
                    </div>
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vai trò</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(auth()->user()->roles as $role)
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('home') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Cập nhật
                </button>
            </div>
        </form>

        {{-- Form riêng để gửi lại email xác thực (nằm ngoài form cập nhật profile) --}}
        @if(!auth()->user()->email_verified_at)
            <form id="resend-verification-form" method="POST" action="{{ route('verification.send') }}" class="hidden">
                @csrf
            </form>
        @endif
    </div>

    <!-- Change Password Section -->
    <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mt-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Đổi mật khẩu</h2>
        
        <form method="POST" action="{{ route('auth.change-password') }}">
            @csrf

            <div class="mb-6">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu hiện tại</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                    class="w-full rounded-md border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('current_password') border-red-500 @else border-gray-300 @enderror"
                >
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    minlength="8"
                    class="w-full rounded-md border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('new_password') border-red-500 @else border-gray-300 @enderror"
                >
                @error('new_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Mật khẩu phải có ít nhất 8 ký tự</p>
            </div>

            <div class="mb-6">
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                <input 
                    type="password" 
                    id="new_password_confirmation" 
                    name="new_password_confirmation" 
                    required
                    minlength="8"
                    class="w-full rounded-md border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('new_password_confirmation') border-red-500 @else border-gray-300 @enderror"
                >
                @error('new_password_confirmation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-4">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Đổi mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

