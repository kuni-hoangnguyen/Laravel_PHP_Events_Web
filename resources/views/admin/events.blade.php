@extends('layouts.app')

@section('title', 'Quản lý sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Admin Events Management</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

