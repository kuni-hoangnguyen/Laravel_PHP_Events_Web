@extends('layouts.app')

@section('title', 'Tạo đánh giá - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $eventId = $event->event_id ?? $event->id;
    @endphp

    <div class="mb-6">
        <a href="{{ route('events.show', $eventId) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Quay lại sự kiện
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tạo đánh giá: {{ $event->title }}</h1>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-blue-800 text-sm">
            <strong>Lưu ý:</strong> Bạn chỉ có thể đánh giá sự kiện này vì bạn đã mua vé và sự kiện đã kết thúc.
        </p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
        <form method="POST" action="{{ route('reviews.store', $eventId) }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Điểm đánh giá <span class="text-red-500">*</span></label>
                <div class="flex gap-2" id="ratingStars">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="hidden" required>
                        <label for="star{{ $i }}" class="text-4xl cursor-pointer text-gray-300 hover:text-yellow-400 transition" data-rating="{{ $i }}">
                            ★
                        </label>
                    @endfor
                </div>
                @error('rating')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                    Bình luận (Tùy chọn, tối đa 500 ký tự)
                </label>
                <textarea 
                    id="comment" 
                    name="comment" 
                    rows="6"
                    maxlength="500"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('comment') border-red-500 @enderror"
                    placeholder="Chia sẻ trải nghiệm của bạn về sự kiện này..."
                >{{ old('comment') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="charCount">0</span> / 500 ký tự
                </p>
                @error('comment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('events.show', $eventId) }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Gửi đánh giá
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#ratingStars label');
    const commentTextarea = document.getElementById('comment');
    const charCount = document.getElementById('charCount');

    // Star rating interaction
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            document.getElementById('star' + rating).checked = true;
            updateStars(rating);
        });

        star.addEventListener('mouseenter', function() {
            const rating = this.dataset.rating;
            updateStars(rating);
        });
    });

    document.getElementById('ratingStars').addEventListener('mouseleave', function() {
        const checked = document.querySelector('input[name="rating"]:checked');
        if (checked) {
            updateStars(checked.value);
        } else {
            updateStars(0);
        }
    });

    function updateStars(rating) {
        stars.forEach((star, index) => {
            const starRating = 5 - index;
            if (starRating <= rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    // Character count
    commentTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
});
</script>
@endsection
