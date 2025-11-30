@extends('layouts.app')

@section('title', 'Vé của tôi - Events Web')

@section('content')
    <div class="container">
        <h1>My Tickets</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
