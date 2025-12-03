@extends('layouts.app')

@section('title', 'Mua vé - ' . ($event->title ?? 'Seniks Events'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Mua vé</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $event->title }}</h2>
        <p class="text-gray-600 mb-6">{{ $event->start_time->format('d/m/Y H:i') }} - {{ $event->location->name ?? 'N/A' }}</p>

        <form method="POST" action="{{ route('tickets.store', $event->event_id ?? $event->id) }}" id="purchaseForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">Chọn loại vé</label>
                @php
                    // $ticketTypes đã được load từ controller với điều kiện
                    $ticketTypes = $event->ticketTypes;
                @endphp
                
                @if($ticketTypes && $ticketTypes->count() > 0)
                    <div class="space-y-3">
                        @foreach($ticketTypes as $ticketType)
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="ticket_type_id" 
                                    value="{{ $ticketType->ticket_type_id }}"
                                    required
                                    class="mr-4 text-indigo-600 focus:ring-indigo-500"
                                    data-price="{{ $ticketType->price }}"
                                    data-remaining="{{ $ticketType->remaining_quantity }}"
                                >
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $ticketType->name }}</h3>
                                            @if($ticketType->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $ticketType->description }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-indigo-600">{{ number_format($ticketType->price) }} đ</p>
                                            <p class="text-sm text-gray-500">Còn lại: {{ $ticketType->remaining_quantity }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Hiện tại không có loại vé nào đang bán.</p>
                @endif
            </div>

            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                    Số lượng <span class="text-red-500">*</span> (Tối đa 10 vé)
                </label>
                <input 
                    type="number" 
                    id="quantity" 
                    name="quantity" 
                    value="1"
                    min="1"
                    max="10"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('quantity') border-red-500 @enderror"
                >
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">Phương thức thanh toán <span class="text-red-500">*</span></label>
                @php
                    // Lấy phương thức thanh toán tiền mặt
                    $cashMethod = \App\Models\PaymentMethod::where('name', 'like', '%Tiền mặt%')
                        ->orWhere('name', 'like', '%Cash%')
                        ->orWhere('name', 'like', '%tiền mặt%')
                        ->first();
                @endphp
                
                @if($cashMethod)
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 cursor-pointer">
                            <input 
                                type="radio" 
                                name="payment_method_id" 
                                value="{{ $cashMethod->method_id }}"
                                required
                                checked
                                class="mr-4 text-indigo-600 focus:ring-indigo-500"
                            >
                            <div class="flex-1">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $cashMethod->name }}</h3>
                                        @if($cashMethod->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $cashMethod->description }}</p>
                                        @else
                                            <p class="text-sm text-gray-600 mt-1">Thanh toán bằng tiền mặt tại sự kiện. Vui lòng chờ tổ chức xác nhận.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        {{-- Skeleton cho các phương thức khác (sẽ thêm sau) --}}
                        <div class="p-4 border-2 border-gray-200 rounded-lg bg-gray-50 opacity-50">
                            <div class="flex items-center">
                                <input type="radio" disabled class="mr-4 text-gray-400">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-400">Phương thức khác</h3>
                                    <p class="text-sm text-gray-400 mt-1">Sẽ được thêm vào sau</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-red-500">Không tìm thấy phương thức thanh toán tiền mặt. Vui lòng liên hệ quản trị viên.</p>
                @endif
                
                @error('payment_method_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-lg font-semibold text-gray-900">Tổng tiền:</span>
                    <span class="text-2xl font-bold text-indigo-600" id="totalPrice">0 đ</span>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Xác nhận mua vé
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('purchaseForm');
    const quantityInput = document.getElementById('quantity');
    const ticketTypeInputs = document.querySelectorAll('input[name="ticket_type_id"]');
    const totalPriceElement = document.getElementById('totalPrice');

    function updateTotal() {
        const selectedTicket = document.querySelector('input[name="ticket_type_id"]:checked');
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (selectedTicket) {
            const price = parseFloat(selectedTicket.dataset.price) || 0;
            const total = price * quantity;
            totalPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
        } else {
            totalPriceElement.textContent = '0 đ';
        }
    }

    ticketTypeInputs.forEach(input => {
        input.addEventListener('change', updateTotal);
    });

    quantityInput.addEventListener('input', updateTotal);
    updateTotal();
});
</script>
@endsection
