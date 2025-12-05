@extends('layouts.app')

@section('title', 'Đánh giá sự kiện - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $eventId = $event->event_id ?? $event->id;
        $avgRating = $reviews->avg('rating');
        $canReview = false;
        if (auth()->check() && auth()->user()->email_verified_at && $event->end_time < now()) {
            $hasTicket = \App\Models\Ticket::where('attendee_id', auth()->id())
                ->whereHas('ticketType', function($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })
                ->where('payment_status', 'paid')
                ->exists();
            $hasReview = \App\Models\Review::where('event_id', $eventId)
                ->where('user_id', auth()->id())
                ->exists();
            $canReview = $hasTicket && !$hasReview;
        }
    @endphp

    <div class="mb-6">
        <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại sự kiện
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Đánh giá: {{ $event->title ?? 'N/A' }}</h1>

    <!-- Rating Summary -->
    @if($reviews->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <span class="text-4xl font-bold text-gray-900 mr-4">{{ number_format($avgRating, 1) }}</span>
                <div>
                    <div class="flex mb-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= $avgRating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600">{{ $reviews->count() }} đánh giá</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Review Form -->
    @if($canReview)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Viết đánh giá</h2>
                <a href="{{ route('reviews.create', $event->event_id ?? $event->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg">
                    ⭐ Tạo đánh giá chi tiết
                </a>
            </div>
            <p class="text-gray-600 text-sm mb-4">Hoặc bạn có thể điền form đánh giá chi tiết bằng cách click vào nút trên.</p>
        </div>
    @endif

    <!-- Reviews List -->
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $review->user->full_name ?? $review->user->name ?? 'N/A' }}</h3>
                            <p class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                    @if($review->comment)
                        <p class="text-gray-700">{{ $review->comment }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Chưa có đánh giá nào.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#ratingStars label');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            document.getElementById('star' + rating).checked = true;
            stars.forEach((s, index) => {
                if (5 - index <= rating) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
    });
});
</script>
@endsection
