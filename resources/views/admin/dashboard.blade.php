@extends('layouts.admin')

@section('title', 'Dashboard Quản trị - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard Quản trị</h1>

    @php
        $totalUsers = \App\Models\User::count();
        $totalEvents = \App\Models\Event::count();
        $pendingEvents = \App\Models\Event::where('approved', 0)->count();
        $totalTickets = \App\Models\Ticket::where('payment_status', 'paid')->sum('quantity');
        $totalRevenue = \App\Models\Payment::where('status', 'success')->sum('amount');
        $recentEvents = \App\Models\Event::latest()->take(5)->get();
        $recentUsers = \App\Models\User::latest()->take(5)->get();
        
    @endphp

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tổng người dùng -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-indigo-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tổng người dùng</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                </div>
            </div>
        </div>

        <!-- Tổng sự kiện -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tổng sự kiện</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEvents) }}</p>
                </div>
            </div>
        </div>

        <!-- Chờ duyệt -->
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

        <!-- Tổng vé -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tổng vé</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTickets) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Tickets Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Doanh thu -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tổng giao dịch</h2>
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
            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalTickets) }}</p>
            <p class="text-sm text-gray-500 mt-2">Tổng số vé đã được thanh toán</p>
        </div>
    </div>

    <!-- Recent Events and Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">5 Sự kiện gần đây</h2>
            <div class="space-y-3">
                @foreach($recentEvents as $event)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $event->title }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-xs text-gray-500">{{ $event->created_at->diffForHumans() }}</p>
                                <span class="text-xs">•</span>
                                <p class="text-xs">
                                    @if($event->approved == 1)
                                        <span class="text-green-600 font-medium">Đã duyệt</span>
                                    @elseif($event->approved == -1)
                                        <span class="text-red-600 font-medium">Đã từ chối</span>
                                    @else
                                        <span class="text-yellow-600 font-medium">Chờ duyệt</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4 shrink-0">
                            <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Xem
                            </a>
                            <a href="{{ route('events.checkin.scanner', $event->event_id ?? $event->id) }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors" title="QR Scanner">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                                Scanner
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">5 Người dùng mới nhất</h2>
            <div class="space-y-3">
                @foreach($recentUsers as $user)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $user->full_name ?? $user->name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-1">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors whitespace-nowrap shrink-0">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Xem
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Revenue per Event -->
    @if(isset($eventsRevenue) && $eventsRevenue->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Doanh thu theo từng sự kiện</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sự kiện</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người tổ chức</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($eventsRevenue as $event)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $event->title ?? $event->event_name }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-600">{{ $event->organizer->full_name ?? $event->organizer->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="text-sm font-semibold text-gray-900">{{ number_format($event->payments_sum_amount ?? 0, 0, ',', '.') }} đ</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
