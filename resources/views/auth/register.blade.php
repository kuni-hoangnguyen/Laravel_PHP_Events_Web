@extends('layouts.app')

@section('title', 'Đăng ký - Events Web')

@section('content')
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-4 text-center">Đăng ký tài khoản</h3>
            <form method="POST" action="/register">
                @csrf
                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ và tên</label>
                    <input name="full_name" type="text" class="form-control" id="full_name" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input name="password" type="password" class="form-control" id="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                    <input name="password_confirmation" type="password" class="form-control" id="password_confirmation" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input name="phone" type="text" class="form-control" id="phone">
                </div>
                <button type="submit" class="btn btn-success w-100">Đăng ký</button>
            </form>
        </div>
    </div>
</div>
@endsection
