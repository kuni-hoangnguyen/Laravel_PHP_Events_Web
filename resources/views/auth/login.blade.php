@extends('layouts.app')

@section('title', 'Đăng nhập - Events Web')

@section('content')
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-4 text-center">Đăng nhập</h3>
            <form method="POST" action="/login">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" id="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input name="password" type="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </div>
</div>
@endsection