@extends('layouts.app')

@section('title', 'Quản trị hệ thống - Events Web')

@section('content')
    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>Stats Data:</h2>
        <pre>{{ json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
