@extends('layouts.app')

@section('title', 'Sự kiện yêu thích - Events Web')

@section('content')
    <div class="container">
        <h1>Favorites</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($favorites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
