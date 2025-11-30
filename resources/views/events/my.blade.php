@extends('layouts.app')

@section('title', 'Sự kiện của tôi - Events Web')

@section('content')
    <div class="container">
        <h1>My Events</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
