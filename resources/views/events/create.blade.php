@extends('layouts.app')

@section('title', 'Tạo sự kiện mới - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tạo sự kiện mới</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <form method="POST" action="{{ route('events.store') }}">
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
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
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
                <label for="banner_url" class="block text-sm font-medium text-gray-700 mb-2">
                    URL Banner/Ảnh đại diện
                </label>
                <input 
                    type="url" 
                    id="banner_url" 
                    name="banner_url" 
                    value="{{ old('banner_url') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('banner_url') border-red-500 @enderror"
                    placeholder="https://example.com/image.jpg"
                >
                @error('banner_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ticket Types Section -->
            <div class="mb-6 border-t border-gray-200 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Loại vé</h3>
                    <button type="button" onclick="addTicketType()" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
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

<script>
let ticketTypeIndex = 0;

function addTicketType(ticketType = null) {
    const container = document.getElementById('ticket-types-container');
    const index = ticketTypeIndex++;
    const ticketTypeHtml = `
        <div class="border border-gray-200 rounded-lg p-4 ticket-type-item" data-index="${index}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-gray-900">Loại vé #${index + 1}</h4>
                <button type="button" onclick="removeTicketType(${index})" class="text-red-600 hover:text-red-800 text-sm">
                    Xóa
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên loại vé <span class="text-red-500">*</span></label>
                    <input type="text" name="ticket_types[${index}][name]" value="${ticketType?.name || ''}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ví dụ: Vé VIP, Vé Thường">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá (VND) <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][price]" value="${ticketType?.price || ''}" required min="0" step="1000"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng số vé <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][total_quantity]" value="${ticketType?.total_quantity || ''}" required min="1"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <input type="text" name="ticket_types[${index}][description]" value="${ticketType?.description || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Mô tả quyền lợi của loại vé">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_start_time]" value="${ticketType?.sale_start_time || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_end_time]" value="${ticketType?.sale_end_time || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-3">
                <label class="flex items-center">
                    <input type="checkbox" name="ticket_types[${index}][is_active]" value="1" ${ticketType?.is_active !== false ? 'checked' : ''}
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Kích hoạt</span>
                </label>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', ticketTypeHtml);
}

function removeTicketType(index) {
    const item = document.querySelector(`.ticket-type-item[data-index="${index}"]`);
    if (item) {
        item.remove();
    }
}

// Thêm một ticket type mặc định khi trang load
document.addEventListener('DOMContentLoaded', function() {
    addTicketType();
});
</script>
@endsection
