@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Danh sách đã check-in</h2>
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
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Người tham dự</th>
                <th>Loại vé</th>
                <th>Thời gian check-in</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checkedInTickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_id }}</td>
                    <td>{{ $ticket->attendee->full_name }}</td>
                    <td>{{ $ticket->ticketType->name }}</td>
                    <td>{{ $ticket->checked_in_at ? $ticket->checked_in_at->format('d/m/Y H:i') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $checkedInTickets->links() }}
</div>
@endsection
