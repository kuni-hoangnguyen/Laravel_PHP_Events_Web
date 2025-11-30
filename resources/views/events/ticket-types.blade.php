@extends('layouts.app')

@section('title', 'Loại vé sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Event Ticket Types</h1>
        <h2>Event ID: {{ $eventId }}</h2>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($ticketTypes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

