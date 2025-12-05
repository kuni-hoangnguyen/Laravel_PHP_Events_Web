@extends('layouts.app')

@section('title', 'Danh mục sự kiện - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Danh mục sự kiện</h1>

    @php
        $categories = \App\Models\EventCategory::all();
    @endphp

    @if($categories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $category->name }}</h3>
                    @if($category->description)
                        <p class="text-gray-600 mb-4">{{ $category->description }}</p>
                    @endif
                    <a href="{{ route('events.index', ['category_id' => $category->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                        Xem sự kiện →
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Chưa có danh mục nào.</p>
        </div>
    @endif
</div>
@endsection
