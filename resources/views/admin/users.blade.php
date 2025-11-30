@extends('layouts.app')

@section('title', 'Quản lý người dùng - Events Web')

@section('content')
    <div class="container">
        <h1>Admin Users Management</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

