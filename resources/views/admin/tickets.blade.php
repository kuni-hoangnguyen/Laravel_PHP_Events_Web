@extends('layouts.admin')

@section('title', 'Quản lý vé - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý vé</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex gap-4">
            <select name="payment_status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                <option value="used" {{ request('payment_status') == 'used' ? 'selected' : '' }}>Đã sử dụng</option>
                <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Tìm kiếm theo QR code, tên, email hoặc sự kiện..."
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md">
                Lọc
            </button>
        </form>
    </div>

    @if($tickets->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người mua</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider max-w-xs">Sự kiện</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại vé</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày mua</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-2.5">
                                    <div class="text-xs font-mono text-gray-900 truncate max-w-[120px]">{{ $ticket->qr_code ?? 'N/A' }}</div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="text-xs font-medium text-gray-900 truncate max-w-[150px]">{{ $ticket->attendee->full_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $ticket->attendee->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-3 py-2.5 max-w-xs">
                                    <div class="text-xs font-medium text-gray-900 truncate">{{ $ticket->ticketType->event->title ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $ticket->ticketType->event->start_time->format('d/m/Y H:i') ?? 'N/A' }}</div>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-gray-900 truncate max-w-[120px]">{{ $ticket->ticketType->name ?? 'N/A' }}</td>
                                <td class="px-3 py-2.5 text-xs text-gray-900">{{ $ticket->quantity ?? 1 }} vé</td>
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    @if($ticket->payment_status == 'paid')
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full bg-green-100 text-green-800">Đã thanh toán</span>
                                    @elseif($ticket->payment_status == 'used')
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full bg-blue-100 text-blue-800">Đã sử dụng</span>
                                    @elseif($ticket->payment_status == 'cancelled')
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full bg-red-100 text-red-800">Đã hủy</span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ thanh toán</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ $ticket->purchase_time->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ $ticket->checked_in_at ? $ticket->checked_in_at->format('d/m/Y H:i') : 'Chưa check-in' }}
                                </td>
                                <td class="px-3 py-2.5 text-xs font-medium">
                                    <a href="{{ route('tickets.show', $ticket->ticket_id) }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Không tìm thấy vé nào.</p>
        </div>
    @endif
</div>
@endsection
