@extends('layouts.app')

@section('title', 'Địa điểm sự kiện - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Địa điểm sự kiện</h1>

    @php
        $locations = \App\Models\EventLocation::all();
    @endphp

    @if($locations->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($locations as $location)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900">{{ $location->name }}</h3>
                    </div>
                    @if($location->address)
                        <p class="text-gray-600 mb-4">{{ $location->address }}</p>
                    @endif
                    <a href="{{ route('events.index', ['location_id' => $location->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                        Xem sự kiện →
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Chưa có địa điểm nào.</p>
        </div>
    @endif
</div>
@endsection
