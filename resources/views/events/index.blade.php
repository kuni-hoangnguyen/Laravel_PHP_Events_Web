@extends('layouts.app')

@section('title', 'Danh sách sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Events Index</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
