@extends('layouts.app')

@section('title', 'Chi tiết vé - Events Web')

@section('content')
    <div class="container">
        <h1>Ticket Details</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($ticket, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

