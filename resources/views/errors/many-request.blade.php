@extends('layouts.app')
@section('title', 'Quên mật khẩu - Events Web')
@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-warning text-center" role="alert">
                    <h4 class="alert-heading">Quá nhiều yêu cầu!</h4>
                    <p>Bạn đã gửi quá nhiều yêu cầu trong một khoảng thời gian ngắn. Vui lòng thử lại sau {{ $retry_after }} giây.</p>
                    <hr>
                    <p class="mb-0">Nếu bạn cần trợ giúp, vui lòng liên hệ với bộ phận hỗ trợ của chúng tôi.</p>
                </div>
            </div>
        </div>
    </div>
@endsection