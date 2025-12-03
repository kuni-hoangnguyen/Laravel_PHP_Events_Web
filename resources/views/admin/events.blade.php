@extends('layouts.admin')

@section('title', 'Quản lý sự kiện - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý sự kiện</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.events.index') }}" class="flex gap-4">
            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tất cả</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                <option value="cancellation" {{ request('status') == 'cancellation' ? 'selected' : '' }}>Yêu cầu hủy</option>
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md">
                Lọc
            </button>
        </form>
    </div>

    @php
        $query = \App\Models\Event::with(['category', 'location', 'organizer']);
        if(request('status') == 'pending') {
            $query->where('approved', 0);
        } elseif(request('status') == 'approved') {
            $query->where('approved', 1);
        } elseif(request('status') == 'rejected') {
            $query->where('approved', -1);
        } elseif(request('status') == 'cancellation') {
            $query->where('cancellation_requested', true)->where('status', '!=', 'cancelled');
        }
        $events = $query->latest()->paginate(15);
    @endphp

    @if($events->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sự kiện</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người tổ chức</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doanh thu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($events as $event)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $event->category->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $event->organizer->full_name ?? $event->organizer->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->start_time->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($event->revenue ?? 0) }} đ</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($event->approved == 1)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                        @elseif($event->approved == -1)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Đã từ chối</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ duyệt</span>
                                        @endif
                                        @if($event->status == 'cancelled')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-600 text-white">Đã hủy</span>
                                        @elseif($event->status == 'upcoming')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sắp diễn ra</span>
                                        @elseif($event->status == 'ongoing')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Đang diễn ra</span>
                                        @elseif($event->status == 'ended')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Đã kết thúc</span>
                                        @endif
                                        @if($event->cancellation_requested)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Chờ duyệt hủy</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex flex-col gap-3">
                                        <!-- Dòng 1: Các nút chính -->
                                        @if($event->approved == 0)
                                            <div class="grid grid-cols-4 gap-2">
                                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Xem
                                                </a>
                                                <a href="{{ route('events.checkin.scanner', $event->event_id ?? $event->id) }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors" title="QR Scanner">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                    </svg>
                                                    Scanner
                                                </a>
                                                <form method="POST" action="{{ route('admin.events.approve', $event->event_id ?? $event->id) }}" class="contents">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Duyệt
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.events.reject', $event->event_id ?? $event->id) }}" class="contents">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Không phù hợp với quy định">
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors" onclick="return confirm('Bạn có chắc muốn từ chối sự kiện này?')">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Từ chối
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-3 gap-2">
                                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Xem
                                                </a>
                                                <a href="{{ route('events.checkin.scanner', $event->event_id ?? $event->id) }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-green-50 text-green-700 hover:bg-green-100 transition-colors" title="QR Scanner">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                    </svg>
                                                    Scanner
                                                </a>
                                                <form method="POST" action="{{ route('admin.events.delete', $event->event_id ?? $event->id) }}" class="contents" onsubmit="return confirm('Bạn có chắc muốn xóa sự kiện này? Hành động này không thể hoàn tác!');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                        <!-- Dòng 2: Các nút xử lý hủy -->
                                        @if($event->cancellation_requested)
                                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-200">
                                                <button type="button" onclick="openCancellationReasonModal({{ $event->event_id }}, '{{ addslashes($event->cancellation_reason) }}', '{{ addslashes($event->title) }}')" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Lý do hủy
                                                </button>
                                                <form method="POST" action="{{ route('admin.events.approve-cancellation', $event->event_id ?? $event->id) }}" class="contents">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-orange-50 text-orange-700 hover:bg-orange-100 transition-colors" onclick="return confirm('Bạn có chắc muốn duyệt yêu cầu hủy sự kiện này?')">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Duyệt hủy
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.events.reject-cancellation', $event->event_id ?? $event->id) }}" class="contents">
                                                    @csrf
                                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors" onclick="return confirm('Bạn có chắc muốn từ chối yêu cầu hủy?')">
                                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Từ chối hủy
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Không có sự kiện nào.</p>
        </div>
    @endif
</div>

<!-- Modal hiển thị lý do hủy -->
<div id="cancellationReasonModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Lý do hủy sự kiện</h3>
                <button onclick="closeCancellationReasonModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Sự kiện:</p>
                <p class="text-sm font-semibold text-gray-900" id="modalEventName"></p>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Lý do hủy:</p>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md border" id="modalReason"></p>
            </div>
            <div class="flex justify-end">
                <button onclick="closeCancellationReasonModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-md transition-colors">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openCancellationReasonModal(eventId, reason, eventName) {
        const modal = document.getElementById('cancellationReasonModal');
        document.getElementById('modalEventName').textContent = eventName;
        document.getElementById('modalReason').textContent = reason || 'Không có lý do';
        modal.classList.remove('hidden');
    }

    function closeCancellationReasonModal() {
        const modal = document.getElementById('cancellationReasonModal');
        modal.classList.add('hidden');
    }

    // Đóng modal khi click bên ngoài
    document.getElementById('cancellationReasonModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancellationReasonModal();
        }
    });
</script>
@endsection
