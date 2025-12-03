@extends('layouts.app')

@section('title', 'Dashboard - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard Tổ chức sự kiện</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tổng số sự kiện -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-indigo-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tổng sự kiện</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
                </div>
            </div>
        </div>

        <!-- Sự kiện đã duyệt -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Đã duyệt</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($approvedEvents) }}</p>
                </div>
            </div>
        </div>

        <!-- Sự kiện chờ duyệt -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Chờ duyệt</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingEvents) }}</p>
                </div>
            </div>
        </div>

        <!-- Thanh toán chờ xác nhận -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-orange-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Thanh toán chờ xác nhận</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalPendingCashPayments) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Tickets Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Doanh thu -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Doanh thu</h2>
                <div class="bg-green-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
            <p class="text-sm text-gray-500 mt-2">Tổng doanh thu từ các thanh toán thành công</p>
        </div>

        <!-- Vé đã bán -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Vé đã bán</h2>
                <div class="bg-blue-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalTicketsSold) }}</p>
            <p class="text-sm text-gray-500 mt-2">Tổng số vé đã được thanh toán</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Thao tác nhanh</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('events.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <div class="bg-indigo-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Tạo sự kiện mới</p>
                    <p class="text-sm text-gray-500">Thêm sự kiện mới vào hệ thống</p>
                </div>
            </a>
            <a href="{{ route('events.my') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <div class="bg-green-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Quản lý sự kiện</p>
                    <p class="text-sm text-gray-500">Xem và quản lý tất cả sự kiện</p>
                </div>
            </a>
            @if($totalPendingCashPayments > 0)
            <a href="{{ route('events.my') }}" class="flex items-center p-4 border border-orange-200 rounded-lg hover:bg-orange-50 transition bg-orange-50">
                <div class="bg-orange-100 rounded-full p-2 mr-3 relative">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Xác nhận thanh toán</p>
                    <p class="text-sm text-gray-500">{{ $totalPendingCashPayments }} thanh toán chờ xác nhận</p>
                </div>
            </a>
            @else
            <div class="flex items-center p-4 border border-gray-200 rounded-lg bg-gray-50 opacity-60">
                <div class="bg-gray-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Xác nhận thanh toán</p>
                    <p class="text-sm text-gray-400">Không có thanh toán chờ xác nhận</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sự kiện sắp diễn ra -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Sự kiện sắp diễn ra</h2>
                <a href="{{ route('events.my') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Xem tất cả →</a>
            </div>
            @if($upcomingEvents->count() > 0)
                <div class="space-y-3">
                    @foreach($upcomingEvents as $event)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $event->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $event->start_time->format('d/m/Y H:i') }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($event->approved == 1)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                        @if($event->status == 'upcoming')
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sắp diễn ra</span>
                                        @elseif($event->status == 'ongoing')
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Đang diễn ra</span>
                                        @endif
                                    @elseif($event->approved == -1)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Đã từ chối</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ duyệt</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="ml-4 shrink-0 text-indigo-600 hover:text-indigo-800 font-medium">
                                Xem
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Không có sự kiện sắp diễn ra</p>
            @endif
        </div>

        <!-- Thanh toán chờ xác nhận -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Thanh toán chờ xác nhận</h2>
                <a href="{{ route('events.my') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Xem tất cả →</a>
            </div>
            @if($eventsWithPendingPayments->count() > 0)
                <div class="space-y-3">
                    @foreach($eventsWithPendingPayments as $event)
                        <div class="flex items-center justify-between p-4 border border-orange-200 rounded-lg hover:bg-orange-50 transition bg-orange-50">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $event->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $event->location->name ?? 'N/A' }}</p>
                                <p class="text-sm text-orange-600 font-medium mt-1">{{ $event->pending_cash_payments_count ?? 0 }} thanh toán</p>
                            </div>
                            <a href="{{ route('events.pending-payments', $event->event_id ?? $event->id) }}" class="ml-4 shrink-0 text-orange-600 hover:text-orange-800 font-semibold">
                                Xác nhận
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Không có thanh toán chờ xác nhận</p>
            @endif
        </div>
    </div>

    <!-- Vé đã bán gần đây -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Vé đã bán gần đây</h2>
            <a href="{{ route('events.my') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Xem tất cả →</a>
        </div>
        @if($recentTickets->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sự kiện</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người mua</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentTickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ticket->ticketType->event->title ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ticket->attendee->full_name ?? $ticket->attendee->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ticket->quantity ?? 1 }} vé</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $ticket->purchase_time->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('events.show', $ticket->ticketType->event->event_id ?? $ticket->ticketType->event->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">Chưa có vé nào được bán</p>
        @endif
    </div>

    <!-- Doanh thu theo từng sự kiện -->
    @if($eventsRevenue->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Doanh thu theo từng sự kiện</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sự kiện</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Danh mục</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($eventsRevenue as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-500">{{ $event->category->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($event->revenue ?? 0, 0, ',', '.') }} đ</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
