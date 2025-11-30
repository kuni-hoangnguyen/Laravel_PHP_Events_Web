@extends('layouts.app')

@section('title', 'Chi tiết sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Event Details</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
