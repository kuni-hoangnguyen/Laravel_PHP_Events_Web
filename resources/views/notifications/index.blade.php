@extends('layouts.app')

@section('title', 'Thông báo - Events Web')

@section('content')
    <div class="container">
        <h1>Notifications</h1>
        <h2>Unread Count:</h2>
        <pre>{{ json_encode($unreadCount, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        <h2>Notifications Data:</h2>
        <pre>{{ json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection
