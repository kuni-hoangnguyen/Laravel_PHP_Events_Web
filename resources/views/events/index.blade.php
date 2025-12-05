@extends('layouts.app')

@section('title', 'Danh sách sự kiện - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Danh sách sự kiện</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('events.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Tên sự kiện..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select 
                    id="status" 
                    name="status"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Tất cả</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp diễn ra</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                <select 
                    id="category_id" 
                    name="category_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\EventCategory::all() as $category)
                        <option value="{{ $category->category_id }}" {{ request('category_id') == $category->category_id ? 'selected' : '' }}>
                            {{ $category->category_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">Địa điểm</label>
                <select 
                    id="location_id" 
                    name="location_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\EventLocation::all() as $location)
                        <option value="{{ $location->location_id }}" {{ request('location_id') == $location->location_id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md"
                >
                    Tìm kiếm
                </button>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($events as $event)
                @php
                    $isEnded = $event->end_time < now();
                @endphp
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col {{ $isEnded ? 'opacity-75' : '' }}">
                    <div class="relative overflow-hidden w-full h-48 {{ $isEnded ? 'bg-gray-500' : 'bg-gray-200' }}">
                        @if($event->banner_url)
                            <img 
                                src="{{ $event->banner_url }}" 
                                alt="{{ $event->title }}" 
                                class="w-full h-48 object-cover transition-transform duration-300 hover:scale-110 {{ $isEnded ? 'opacity-60 grayscale' : '' }}"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >
                            <div class="hidden w-full h-48 {{ $isEnded ? 'bg-gray-500' : 'bg-gray-300' }}">
                                <div class="w-full h-full bg-gradient-to-r {{ $isEnded ? 'from-gray-500 via-gray-400 to-gray-500' : 'from-gray-300 via-gray-200 to-gray-300' }} bg-[length:200%_100%] shimmer"></div>
                            </div>
                        @else
                            <div class="w-full h-48 {{ $isEnded ? 'bg-gray-500' : 'bg-gray-300' }}">
                                <div class="w-full h-full bg-gradient-to-r {{ $isEnded ? 'from-gray-500 via-gray-400 to-gray-500' : 'from-gray-300 via-gray-200 to-gray-300' }} bg-[length:200%_100%] shimmer"></div>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4">
                            @if($isEnded)
                                <span class="px-3 py-1 bg-gray-600 text-white rounded-full text-xs font-semibold">Đã kết thúc</span>
                            @elseif($event->start_time <= now() && $event->end_time >= now())
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-xs font-semibold">Đang diễn ra</span>
                            @else
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-semibold">Sắp diễn ra</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6 flex flex-col grow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 {{ $isEnded ? 'bg-gray-100 text-gray-600' : 'bg-indigo-100 text-indigo-600' }} rounded-full text-xs font-semibold">{{ $event->category->name ?? 'N/A' }}</span>
                            <span class="text-sm text-gray-500 font-medium">{{ $event->start_time->format('d/m/Y') }}</span>
                        </div>
                        <h3 class="text-xl font-bold {{ $isEnded ? 'text-gray-600' : 'text-gray-900' }} mb-3 line-clamp-2">{{ $event->title }}</h3>
                        <p class="{{ $isEnded ? 'text-gray-500' : 'text-gray-600' }} text-sm mb-4 line-clamp-2 grow">{{ Str::limit($event->description, 100) }}</p>
                        <div class="mt-auto">
                            <div class="flex items-center text-sm {{ $isEnded ? 'text-gray-400' : 'text-gray-500' }} mb-4">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ $event->location->name ?? 'N/A' }}</span>
                            </div>
                            @if($isEnded && auth()->check())
                                <div class="flex gap-2">
                                    <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Xem chi tiết
                                    </a>
                                    <a href="{{ route('events.reviews', $event->event_id ?? $event->id) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                        Đánh giá
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('events.show', $event->event_id ?? $event->id) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                    Xem chi tiết
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Không tìm thấy sự kiện nào.</p>
        </div>
    @endif
</div>
@endsection
