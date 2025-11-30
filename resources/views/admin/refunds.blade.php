@extends('layouts.app')

@section('title', 'Quản lý hoàn tiền - Events Web')

@section('content')
    <div class="container">
        <h1>Admin Refunds Management</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($refunds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

