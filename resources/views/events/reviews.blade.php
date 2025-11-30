@extends('layouts.app')

@section('title', 'Đánh giá sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Event Reviews</h1>
        <h2>Event Data:</h2>
        <pre>{{ json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        <h2>Reviews Data:</h2>
        <pre>{{ json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

