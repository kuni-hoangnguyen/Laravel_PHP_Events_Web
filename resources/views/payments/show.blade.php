@extends('layouts.app')

@section('title', 'Chi tiết thanh toán - Events Web')

@section('content')
    <div class="container">
        <h1>Payment Details</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($payment, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

