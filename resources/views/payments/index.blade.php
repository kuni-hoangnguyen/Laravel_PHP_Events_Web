@extends('layouts.app')

@section('title', 'Lịch sử thanh toán - Events Web')

@section('content')
    <div class="container">
        <h1>Payments History</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($payments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
