@extends('layouts.app')

@section('title', 'Sự kiện của tôi - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Sự kiện của tôi</h1>
        <a href="{{ route('events.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg">
            + Tạo sự kiện mới
        </a>
    </div>

    <!-- Tabs để filter sự kiện -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('events.my') }}" class="{{ !request()->has('status') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Tất cả sự kiện
            </a>
            <a href="{{ route('events.my', ['status' => 'ended']) }}" class="{{ request()->get('status') == 'ended' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Đã kết thúc
                @if(isset($endedEventsCount) && $endedEventsCount > 0)
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $endedEventsCount }}</span>
                @endif
            </a>
            <a href="{{ route('events.my', ['status' => 'cancelled']) }}" class="{{ request()->get('status') == 'cancelled' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Đã hủy
                @if(isset($cancelledEventsCount) && $cancelledEventsCount > 0)
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $cancelledEventsCount }}</span>
                @endif
            </a>
        </nav>
    </div>

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($events as $event)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <div class="relative overflow-hidden w-full h-48 bg-gray-200">
                        @if($event->banner_url)
                            <img 
                                src="{{ $event->banner_url }}" 
                                alt="{{ $event->title }}" 
                                class="w-full h-48 object-cover transition-transform duration-300 hover:scale-110"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >
                            <div class="hidden w-full h-48 bg-gray-300">
                                <div class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer"></div>
                            </div>
                        @else
                            <div class="w-full h-48 bg-gray-300">
                                <div class="w-full h-full bg-gradient-to-r from-gray-300 via-gray-200 to-gray-300 bg-[length:200%_100%] shimmer"></div>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 flex flex-col gap-2">
                            @if($event->status == 'cancelled')
                                <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-semibold">Đã hủy</span>
                            @elseif($event->end_time < now())
                                <span class="px-3 py-1 bg-gray-600 text-white rounded-full text-xs font-semibold">Đã kết thúc</span>
                            @elseif($event->approved == 1)
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Đã duyệt</span>
                            @elseif($event->approved == -1)
                                <span class="px-3 py-1 bg-red-500 text-white rounded-full text-xs font-semibold">Đã từ chối</span>
                            @else
                                <span class="px-3 py-1 bg-yellow-500 text-white rounded-full text-xs font-semibold">Chờ duyệt</span>
                            @endif
                            @if($event->status == 'upcoming')
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-semibold">Sắp diễn ra</span>
                            @elseif($event->status == 'ongoing')
                                <span class="px-3 py-1 bg-purple-500 text-white rounded-full text-xs font-semibold">Đang diễn ra</span>
                            @endif
                            @if($event->cancellation_requested)
                                <span class="px-3 py-1 bg-orange-500 text-white rounded-full text-xs font-semibold">Chờ duyệt hủy</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6 flex flex-col grow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-xs font-semibold">{{ $event->category->name ?? 'N/A' }}</span>
                            <span class="text-sm text-gray-500 font-medium">{{ $event->start_time->format('d/m/Y') }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">{{ $event->title }}</h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 grow">{{ Str::limit($event->description, 100) }}</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $event->start_time->format('H:i') }}
                            </div>
                            <div class="flex gap-2 mb-2">
                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Xem
                                </a>
                                @if($event->start_time > now() && $event->status != 'cancelled' && !$event->cancellation_requested)
                                    <a href="{{ route('events.edit', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Sửa
                                    </a>
                                @endif
                            </div>
                            @if($event->start_time > now() && $event->status != 'cancelled' && !$event->cancellation_requested && $event->approved == 1)
                                <div class="mb-2">
                                    <button onclick="openCancellationModal({{ $event->event_id }})" class="w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Hủy sự kiện
                                    </button>
                                </div>
                            @endif
                            @if($event->end_time < now())
                                <div class="mb-2">
                                    <a href="{{ route('events.reviews', $event->event_id ?? $event->id) }}" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Xem đánh giá
                                    </a>
                                </div>
                            @endif
                            @php
                                $pendingCount = $event->pending_cash_payments_count ?? 0;
                            @endphp
                            <a href="{{ route('events.pending-payments', $event->event_id ?? $event->id) }}" class="block w-full text-center bg-orange-100 hover:bg-orange-200 text-orange-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200 relative">
                                Thanh toán tiền mặt chờ xác nhận
                                @if($pendingCount > 0)
                                    <span class="absolute top-1 right-1 inline-flex items-center justify-center h-5 w-5 rounded-full bg-red-500 text-white text-xs font-bold">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg mb-4">Bạn chưa tạo sự kiện nào.</p>
            <a href="{{ route('events.create') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                Tạo sự kiện đầu tiên →
            </a>
        </div>
    @endif
</div>

<!-- Modal hủy sự kiện -->
<div id="cancellationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Yêu cầu hủy sự kiện</h3>
            <form id="cancellationForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">Lý do hủy sự kiện <span class="text-red-500">*</span></label>
                    <textarea 
                        id="cancellation_reason" 
                        name="cancellation_reason" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Vui lòng nhập lý do hủy sự kiện (tối thiểu 10 ký tự)"
                        required
                        minlength="10"
                        maxlength="1000"
                    ></textarea>
                    <p class="mt-1 text-sm text-gray-500">Tối thiểu 10 ký tự, tối đa 1000 ký tự</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition-colors">
                        Gửi yêu cầu
                    </button>
                    <button type="button" onclick="closeCancellationModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-md transition-colors">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCancellationModal(eventId) {
        const modal = document.getElementById('cancellationModal');
        const form = document.getElementById('cancellationForm');
        form.action = '{{ route("events.request-cancellation", ":id") }}'.replace(':id', eventId);
        modal.classList.remove('hidden');
    }

    function closeCancellationModal() {
        const modal = document.getElementById('cancellationModal');
        const form = document.getElementById('cancellationForm');
        form.reset();
        modal.classList.add('hidden');
    }

    // Đóng modal khi click bên ngoài
    document.getElementById('cancellationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancellationModal();
        }
    });
</script>
@endsection
