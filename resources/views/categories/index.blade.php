@extends('layouts.app')

@section('title', 'Danh mục sự kiện - Events Web')

@section('content')
    <div class="container">
        <h1>Categories</h1>
        <h2>Data Passed:</h2>
        <pre>{{ json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endsection

