@extends('layouts.app')

@section('title', 'Tạo sự kiện mới - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tạo sự kiện mới</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Tiêu đề sự kiện <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title') }}"
                    required
                    maxlength="200"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
                    placeholder="Nhập tiêu đề sự kiện"
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Mô tả chi tiết
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="6"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                    placeholder="Nhập mô tả chi tiết về sự kiện"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Thời gian bắt đầu <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="start_time" 
                        name="start_time" 
                        value="{{ old('start_time') }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_time') border-red-500 @enderror"
                    >
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Thời gian kết thúc <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="end_time" 
                        name="end_time" 
                        value="{{ old('end_time') }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_time') border-red-500 @enderror"
                    >
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Danh mục <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category_id" 
                        name="category_id"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror"
                    >
                        <option value="">Chọn danh mục</option>
                        @foreach(\App\Models\EventCategory::all() as $category)
                            <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Địa điểm <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="location_id" 
                        name="location_id"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('location_id') border-red-500 @enderror"
                    >
                        <option value="">Chọn địa điểm</option>
                        @foreach(\App\Models\EventLocation::all() as $location)
                            <option value="{{ $location->location_id }}" {{ old('location_id') == $location->location_id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="max_attendees" class="block text-sm font-medium text-gray-700 mb-2">
                    Số lượng người tham gia tối đa
                </label>
                <input 
                    type="number" 
                    id="max_attendees" 
                    name="max_attendees" 
                    value="{{ old('max_attendees') }}"
                    min="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_attendees') border-red-500 @enderror"
                    placeholder="Không giới hạn nếu để trống"
                >
                @error('max_attendees')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-2">
                    Banner/Ảnh đại diện
                </label>
                <input 
                    type="file" 
                    id="banner_image" 
                    name="banner_image" 
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('banner_image') border-red-500 @enderror"
                    onchange="window.previewImage(this, 'banner-preview')"
                >
                <p class="mt-1 text-sm text-gray-500">Chấp nhận: JPEG, PNG, GIF, WebP. Kích thước tối đa: 5MB</p>
                @error('banner_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div id="banner-preview" class="mt-4 hidden">
                    <img id="banner-preview-img" src="" alt="Preview" class="max-w-full h-64 object-cover rounded-md border border-gray-300">
                </div>
            </div>

            <div class="mb-6">
                <label for="banner_url" class="block text-sm font-medium text-gray-700 mb-2">
                    Hoặc nhập URL Banner/Ảnh đại diện
                </label>
                <input 
                    type="url" 
                    id="banner_url" 
                    name="banner_url" 
                    value="{{ old('banner_url') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('banner_url') border-red-500 @enderror"
                    placeholder="https://example.com/image.jpg"
                >
                <p class="mt-1 text-sm text-gray-500">Nếu bạn đã có URL ảnh, có thể nhập trực tiếp</p>
                @error('banner_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ticket Types Section -->
            <div class="mb-6 border-t border-gray-200 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Loại vé</h3>
                    <button type="button" onclick="window.addTicketType()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                        + Thêm loại vé
                    </button>
                </div>
                <div id="ticket-types-container" class="space-y-4">
                    <!-- Ticket types will be added here dynamically -->
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('events.my') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Tạo sự kiện
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.initTicketTypes) {
        window.initTicketTypes(0);
    }
});
</script>
@endpush
@endsection
