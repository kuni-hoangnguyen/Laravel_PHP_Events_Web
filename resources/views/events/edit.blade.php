@extends('layouts.app')

@section('title', 'Chỉnh sửa sự kiện - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        // $event được truyền từ controller qua middleware event.owner
    @endphp

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Chỉnh sửa sự kiện</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <form method="POST" action="{{ route('events.update', $event->event_id ?? $event->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Tiêu đề sự kiện <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title', $event->title) }}"
                    required
                    maxlength="200"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
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
                >{{ old('description', $event->description) }}</textarea>
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
                        value="{{ old('start_time', $event->start_time ? $event->start_time->format('Y-m-d\TH:i') : '') }}"
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
                        value="{{ old('end_time', $event->end_time ? $event->end_time->format('Y-m-d\TH:i') : '') }}"
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
                            <option value="{{ $category->category_id }}" {{ old('category_id', $event->category_id) == $category->category_id ? 'selected' : '' }}>
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
                            <option value="{{ $location->location_id }}" {{ old('location_id', $event->location_id) == $location->location_id ? 'selected' : '' }}>
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
                    value="{{ old('max_attendees', $event->max_attendees) }}"
                    min="1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_attendees') border-red-500 @enderror"
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
                    value="{{ old('banner_url', $event->banner_url) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('banner_url') border-red-500 @enderror"
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
                    @php
                        $ticketTypes = $event->ticketTypes ?? collect();
                    @endphp
                    @foreach($ticketTypes as $ticketType)
                        <div class="border border-gray-200 rounded-lg p-4 ticket-type-item" data-index="{{ $loop->index }}" data-ticket-type-id="{{ $ticketType->ticket_type_id }}">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-900">Loại vé #{{ $loop->index + 1 }}</h4>
                                <button type="button" onclick="removeTicketType({{ $loop->index }})" class="text-red-600 hover:text-red-800 text-sm">
                                    Xóa
                                </button>
                            </div>
                            <input type="hidden" name="ticket_types[{{ $loop->index }}][ticket_type_id]" value="{{ $ticketType->ticket_type_id }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên loại vé <span class="text-red-500">*</span></label>
                                    <input type="text" name="ticket_types[{{ $loop->index }}][name]" value="{{ old("ticket_types.{$loop->index}.name", $ticketType->name) }}" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Ví dụ: Vé VIP, Vé Thường">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá (VND) <span class="text-red-500">*</span></label>
                                    <input type="number" name="ticket_types[{{ $loop->index }}][price]" value="{{ old("ticket_types.{$loop->index}.price", $ticketType->price) }}" required min="0" step="1000"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng số vé <span class="text-red-500">*</span></label>
                                    <input type="number" name="ticket_types[{{ $loop->index }}][total_quantity]" value="{{ old("ticket_types.{$loop->index}.total_quantity", $ticketType->total_quantity) }}" required min="1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                                    <input type="text" name="ticket_types[{{ $loop->index }}][description]" value="{{ old("ticket_types.{$loop->index}.description", $ticketType->description) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Mô tả quyền lợi của loại vé">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu bán</label>
                                    <input type="datetime-local" name="ticket_types[{{ $loop->index }}][sale_start_time]" 
                                        value="{{ old("ticket_types.{$loop->index}.sale_start_time", $ticketType->sale_start_time ? $ticketType->sale_start_time->format('Y-m-d\TH:i') : '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc bán</label>
                                    <input type="datetime-local" name="ticket_types[{{ $loop->index }}][sale_end_time]" 
                                        value="{{ old("ticket_types.{$loop->index}.sale_end_time", $ticketType->sale_end_time ? $ticketType->sale_end_time->format('Y-m-d\TH:i') : '') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="ticket_types[{{ $loop->index }}][is_active]" value="1" {{ old("ticket_types.{$loop->index}.is_active", $ticketType->is_active) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Kích hoạt</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('events.my') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let ticketTypeIndex = {{ $event->ticketTypes->count() ?? 0 }};

function addTicketType() {
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
                    <input type="text" name="ticket_types[${index}][name]" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ví dụ: Vé VIP, Vé Thường">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá (VND) <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][price]" required min="0" step="1000"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng số vé <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][total_quantity]" required min="1"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <input type="text" name="ticket_types[${index}][description]"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Mô tả quyền lợi của loại vé">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_start_time]"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_end_time]"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-3">
                <label class="flex items-center">
                    <input type="checkbox" name="ticket_types[${index}][is_active]" value="1" checked
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
        const ticketTypeId = item.getAttribute('data-ticket-type-id');
        if (ticketTypeId) {
            // Nếu là ticket type đã tồn tại, thêm hidden input để đánh dấu xóa
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = `ticket_types[${index}][_delete]`;
            deleteInput.value = '1';
            item.appendChild(deleteInput);
            item.style.display = 'none';
        } else {
            item.remove();
        }
    }
}
</script>
@endsection
