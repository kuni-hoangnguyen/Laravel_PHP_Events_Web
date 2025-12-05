@extends('layouts.admin')

@section('title', 'Quản lý hoàn tiền - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý hoàn tiền</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="flex gap-4">
            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md">
                Lọc
            </button>
        </form>
    </div>


    @if($refunds->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Người yêu cầu</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Sự kiện</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Số tiền</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do</th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Trạng thái</th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($refunds as $refund)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-5">
                                    <div class="text-sm font-medium text-gray-900">{{ $refund->requester->full_name ?? $refund->requester->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $refund->requester->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="text-sm text-gray-900 line-clamp-2">{{ $refund->payment->ticket->ticketType->event->title ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="text-sm font-semibold text-gray-900">{{ number_format($refund->payment->amount) }} đ</div>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="text-sm text-gray-700 leading-relaxed max-w-md">{{ $refund->reason }}</div>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    @if($refund->status == 'approved')
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                    @elseif($refund->status == 'rejected')
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Đã từ chối</span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ xử lý</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5 text-center">
                                    @if($refund->status == 'pending')
                                        <div class="flex flex-col gap-2">
                                            <form method="POST" action="{{ route('admin.refunds.process', $refund->refund_id) }}" class="contents">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Duyệt
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.refunds.process', $refund->refund_id) }}" class="contents">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Từ chối
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $refunds->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Không có yêu cầu hoàn tiền nào.</p>
        </div>
    @endif
</div>
@endsection
