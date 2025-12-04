@extends('layouts.app')

@section('title', 'Chi tiết vé - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        // $ticket được truyền từ controller qua middleware ticket.owner
    @endphp

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Chi tiết vé</h1>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $ticket->ticketType->event->title ?? 'N/A' }}</h2>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        @if($ticket->ticketType->event)
                            {{ $ticket->ticketType->event->start_time->format('d/m/Y H:i') }} - {{ $ticket->ticketType->event->end_time->format('d/m/Y H:i') }}
                        @else
                            Sự kiện đã bị xóa
                        @endif
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $ticket->ticketType->event->location->name ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600 mb-1">Loại vé</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $ticket->ticketType->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600 mb-1">Số lượng</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $ticket->quantity ?? 1 }} vé</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600 mb-1">Giá vé (đơn vị)</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($ticket->ticketType->price ?? 0) }} đ</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600 mb-1">Tổng tiền</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format(($ticket->ticketType->price ?? 0) * ($ticket->quantity ?? 1)) }} đ</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Trạng thái</p>
                    @if($ticket->payment_status == 'paid')
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Đã thanh toán</span>
                    @elseif($ticket->payment_status == 'used')
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Đã sử dụng</span>
                    @elseif($ticket->payment_status == 'cancelled')
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Đã hủy</span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Chờ thanh toán</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code - Chỉ hiển thị khi đã thanh toán -->
    @if($ticket->payment_status == 'paid')
        <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">QR Code vé</h2>
            <div class="flex justify-center mb-4">
                <img src="{{ $qrImageUrl }}" alt="QR Code" class="w-80 h-80 border-4 border-gray-200 rounded-lg shadow-sm">
            </div>
            <p class="text-sm text-gray-600 mb-2">Mã QR: <span class="font-semibold text-indigo-600">{{ $qrCode }}</span></p>
            @if(($ticket->quantity ?? 1) > 1)
                <p class="text-sm text-gray-600 mb-2">Số lượng vé: <span class="font-semibold text-indigo-600">{{ $ticket->quantity }} vé</span></p>
            @endif
            <p class="text-sm text-gray-600">Hiển thị QR code này tại sự kiện để check-in</p>
            <div class="mt-4">
                <a href="{{ route('tickets.qr', $ticket->ticket_id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                    Xem chi tiết QR code →
                </a>
            </div>
        </div>
    @elseif($ticket->payment_status == 'cancelled')
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 md:p-8 mb-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-red-800 mb-2">Vé đã bị hủy</h2>
            <p class="text-red-700 mb-4">Vé của bạn đã bị từ chối thanh toán bởi tổ chức sự kiện. Vé này không còn hiệu lực.</p>
            <p class="text-sm text-red-600">Nếu bạn đã thanh toán, vui lòng liên hệ với tổ chức sự kiện để được hỗ trợ.</p>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 md:p-8 mb-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-yellow-800 mb-2">Vé chưa được xác nhận thanh toán</h2>
            <p class="text-yellow-700 mb-4">Vé của bạn đang chờ tổ chức xác nhận thanh toán. Sau khi được xác nhận, QR code và thông tin vé sẽ được hiển thị tại đây.</p>
            <p class="text-sm text-yellow-600">Bạn sẽ nhận được email thông báo khi thanh toán được xác nhận.</p>
        </div>
    @endif

    <!-- Payment Info -->
    @if($ticket->payment)
        <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Thông tin thanh toán</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Số tiền</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($ticket->payment->amount) }} đ</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Phương thức</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $ticket->payment->paymentMethod->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Trạng thái giao dịch</p>
                    @if($ticket->payment->status == 'success')
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Thành công</span>
                    @elseif($ticket->payment->status == 'refunded')
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Đã hoàn tiền</span>
                    @elseif($ticket->payment->status == 'failed')
                        @if($ticket->payment_status == 'cancelled')
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Đã từ chối</span>
                        @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Chờ xác nhận</span>
                        @endif
                    @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Thất bại</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Transaction ID</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $ticket->payment->transaction_id ?? 'N/A' }}</p>
                </div>
            </div>

            @php
                $hasRefund = \App\Models\Refund::where('payment_id', $ticket->payment->payment_id)->exists();
            @endphp
            @if($ticket->payment->status == 'success' && !$hasRefund && $ticket->payment_status == 'paid')
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Yêu cầu hoàn tiền</h3>
                    <form method="POST" action="{{ route('payments.refund', $ticket->payment->payment_id) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Lý do hoàn tiền</label>
                            <textarea 
                                id="reason" 
                                name="reason" 
                                required
                                rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Nhập lý do yêu cầu hoàn tiền..."
                            ></textarea>
                        </div>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg">
                            Yêu cầu hoàn tiền
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
