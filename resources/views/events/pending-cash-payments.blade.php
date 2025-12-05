@extends('layouts.app')

@section('title', 'Thanh toán tiền mặt chờ xác nhận - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Thanh toán tiền mặt chờ xác nhận</h1>
        <p class="text-gray-600 mt-2">Sự kiện: <span class="font-semibold">{{ $event->title }}</span></p>
    </div>

    @if($pendingPayments->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người mua</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại vé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingPayments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $payment->ticket->attendee->full_name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $payment->ticket->attendee->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->ticket->ticketType->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->ticket->quantity ?? 1 }} vé</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($payment->amount) }} đ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->transaction_id ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->ticket->purchase_time ? $payment->ticket->purchase_time->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="grid grid-cols-2 gap-2">
                                        <form method="POST" action="{{ route('payments.confirm-cash', $payment->payment_id) }}" class="contents">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Xác nhận
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('payments.reject-cash', $payment->payment_id) }}" class="contents" onsubmit="return confirm('Bạn có chắc muốn từ chối thanh toán này? Vé sẽ bị hủy và số lượng vé sẽ được hoàn lại.')">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $pendingPayments->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg mb-4">Không có thanh toán tiền mặt nào chờ xác nhận.</p>
            <a href="{{ route('events.my') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                Quay lại danh sách sự kiện →
            </a>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('events.my') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
            ← Quay lại danh sách sự kiện
        </a>
    </div>
</div>
@endsection

