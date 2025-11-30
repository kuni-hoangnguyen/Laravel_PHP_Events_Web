@extends('layouts.app')

@section('title', 'Nhật ký quản trị - Events Web')

@section('content')
    <div class="container">
        <h1>Admin Logs</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

