@extends('layouts.app')

@section('title', 'Địa điểm sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Locations</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($locations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

