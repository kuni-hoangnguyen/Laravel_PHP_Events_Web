@extends('layouts.app')
@section('content')
<div class="container">
    <h2>QR Code Vé</h2>
    @if(session('success'))
        <div class="toast align-items-center text-bg-success border-0 show position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index:9999;">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="toast align-items-center text-bg-danger border-0 show position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index:9999;">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
    <div class="card mb-3">
        <div class="card-body text-center">
            <img src="{{ $qrImageUrl }}" alt="QR Code" class="mb-3" />
            <p><strong>Mã QR:</strong> {{ $qrCode }}</p>
            <p><strong>Sự kiện:</strong> {{ $ticket->ticketType->event->title ?? 'N/A' }}</p>
            <p><strong>Loại vé:</strong> {{ $ticket->ticketType->name }}</p>
            <p><strong>Người tham dự:</strong> {{ $ticket->attendee->full_name }}</p>
        </div>
    </div>
</div>
@endsection
