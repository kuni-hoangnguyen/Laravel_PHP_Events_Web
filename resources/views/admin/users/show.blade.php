@extends('layouts.admin')

@section('title', 'Chi tiết người dùng - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại danh sách người dùng
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Chi tiết người dùng</h1>

    <!-- User Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-20 h-20 rounded-full object-cover border-4 border-indigo-500 mr-4">
                @else
                    <div class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-white text-2xl font-bold border-4 border-indigo-500 mr-4">
                        {{ strtoupper(substr($user->full_name ?? $user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->full_name ?? $user->name ?? 'N/A' }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <p class="text-sm text-gray-500 mt-1">Đăng ký: {{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($user->roles as $role)
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                        {{ $role->role_name }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Sự kiện đã tổ chức</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Vé đã mua</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTickets) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Đánh giá</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalReviews) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Tổng chi tiêu</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="showTab('events')" id="tab-events" class="tab-button active px-6 py-3 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600">
                    Sự kiện đã tổ chức ({{ $user->organizedEvents->count() }})
                </button>
                <button onclick="showTab('tickets')" id="tab-tickets" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    Vé đã mua ({{ $user->tickets->count() }})
                </button>
                <button onclick="showTab('payments')" id="tab-payments" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    Thanh toán ({{ $payments->total() }})
                </button>
                <button onclick="showTab('reviews')" id="tab-reviews" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    Đánh giá ({{ $user->reviews->count() }})
                </button>
                <button onclick="showTab('favorites')" id="tab-favorites" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                    Yêu thích ({{ $user->favoriteEvents->count() }})
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Events Tab -->
            <div id="content-events" class="tab-content">
                @if($user->organizedEvents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên sự kiện</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Danh mục</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Địa điểm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày tạo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($user->organizedEvents as $event)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->category->category_name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->location->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($event->approved == 1)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                            @elseif($event->approved == -1)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Từ chối</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ duyệt</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('events.show', $event->event_id) }}" class="text-indigo-600 hover:text-indigo-900">Xem</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Người dùng này chưa tổ chức sự kiện nào.</p>
                @endif
            </div>

            <!-- Tickets Tab -->
            <div id="content-tickets" class="tab-content hidden">
                @if($user->tickets->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sự kiện</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loại vé</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số lượng</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày mua</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($user->tickets as $ticket)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $ticket->ticketType->event->title ?? 'Sự kiện đã bị xóa' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->ticketType->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->quantity ?? 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($ticket->payment_status == 'paid')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã thanh toán</span>
                                            @elseif($ticket->payment_status == 'used')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Đã sử dụng</span>
                                            @elseif($ticket->payment_status == 'cancelled')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Đã hủy</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ thanh toán</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->purchase_time->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('tickets.show', $ticket->ticket_id) }}" class="text-indigo-600 hover:text-indigo-900">Xem</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Người dùng này chưa mua vé nào.</p>
                @endif
            </div>

            <!-- Payments Tab -->
            <div id="content-payments" class="tab-content hidden">
                @if($payments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sự kiện</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số tiền</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phương thức</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày thanh toán</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $payment->ticket->ticketType->event->title ?? 'Sự kiện đã bị xóa' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($payment->amount) }} đ</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->paymentMethod->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($payment->status == 'success')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Thành công</span>
                                            @elseif($payment->status == 'refunded')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Đã hoàn tiền</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ xác nhận</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Người dùng này chưa có giao dịch thanh toán nào.</p>
                @endif
            </div>

            <!-- Reviews Tab -->
            <div id="content-reviews" class="tab-content hidden">
                @if($user->reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->reviews as $review)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $review->event->title ?? 'Sự kiện đã bị xóa' }}</h4>
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-700 mt-2">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Người dùng này chưa có đánh giá nào.</p>
                @endif
            </div>

            <!-- Favorites Tab -->
            <div id="content-favorites" class="tab-content hidden">
                @if($user->favoriteEvents->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($user->favoriteEvents as $event)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-2">{{ $event->title }}</h4>
                                <p class="text-sm text-gray-500 mb-2">{{ $event->category->category_name ?? 'N/A' }}</p>
                                <a href="{{ route('events.show', $event->event_id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Xem sự kiện →</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Người dùng này chưa có sự kiện yêu thích nào.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    const tabs = ['events', 'tickets', 'payments', 'reviews', 'favorites'];
    
    tabs.forEach(tab => {
        const tabButton = document.getElementById('tab-' + tab);
        const tabContent = document.getElementById('content-' + tab);
        
        if (tab === tabName) {
            tabButton.classList.add('active', 'text-indigo-600', 'border-indigo-600');
            tabButton.classList.remove('text-gray-500', 'border-transparent');
            tabContent.classList.remove('hidden');
        } else {
            tabButton.classList.remove('active', 'text-indigo-600', 'border-indigo-600');
            tabButton.classList.add('text-gray-500', 'border-transparent');
            tabContent.classList.add('hidden');
        }
    });
}
</script>
@endsection

