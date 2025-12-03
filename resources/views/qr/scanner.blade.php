@extends('layouts.app')

@section('title', 'QR Scanner - Seniks Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">QR Scanner - Check-in</h1>
            @if(isset($event))
                <p class="text-gray-600 mt-1">{{ $event->title }}</p>
            @endif
        </div>
        @if(isset($event))
            <div class="flex gap-2">
                <a href="{{ route('events.checkin.stats', $event->event_id ?? $event->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Thống kê
                </a>
                <a href="{{ route('events.checkin.attendees', $event->event_id ?? $event->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Danh sách
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.events.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                            Quay lại
                        </a>
                    @else
                        <a href="{{ route('events.my') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                            Quay lại
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-6">
        <div class="mb-6">
            <label for="cam-list" class="block text-sm font-medium text-gray-700 mb-2">
                Chọn camera
            </label>
            <select id="cam-list" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Đang tải...</option>
            </select>
        </div>

        <div id="video-container" class="mb-6">
            <video id="qr-video" class="w-full rounded-lg border-2 border-gray-200" autoplay playsinline></video>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-gray-600 mb-1">QR Code đã quét:</p>
            <p class="text-lg font-semibold text-gray-900" id="cam-qr-result">Chưa có</p>
        </div>

        <form action="{{ route('events.checkin', $eventId) }}" method="POST" id="qr-checkin-form" class="hidden">
            @csrf
            <input type="hidden" name="qr_code" id="qr_data_input">
        </form>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm text-blue-800 font-semibold mb-1">Hướng dẫn</p>
                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li>Chọn camera từ danh sách</li>
                    <li>Đưa QR code vào khung hình</li>
                    <li>Hệ thống sẽ tự động quét và check-in</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('qr-video');
    const camList = document.getElementById('cam-list');
    const qrResult = document.getElementById('cam-qr-result');
    const qrDataInput = document.getElementById('qr_data_input');
    const checkinForm = document.getElementById('qr-checkin-form');
    let qrScanner = null;

    // Load cameras
    QrScanner.listCameras(true).then(cameras => {
        if (cameras.length === 0) {
            camList.innerHTML = '<option value="">Không tìm thấy camera</option>';
            return;
        }

        camList.innerHTML = '<option value="">Chọn camera</option>';
        cameras.forEach((camera, index) => {
            const option = document.createElement('option');
            option.value = camera.id;
            option.textContent = camera.label || `Camera ${index + 1}`;
            camList.appendChild(option);
        });

        // Auto select first camera
        if (cameras.length > 0) {
            camList.value = cameras[0].id;
            startScanner(cameras[0].id);
        }
    }).catch(err => {
        console.error('Error loading cameras:', err);
        camList.innerHTML = '<option value="">Lỗi tải camera</option>';
    });

    // Camera change handler
    camList.addEventListener('change', function() {
        if (this.value) {
            startScanner(this.value);
        } else {
            stopScanner();
        }
    });

    function startScanner(cameraId) {
        stopScanner();

        qrScanner = new QrScanner(
            video,
            result => {
                qrResult.textContent = result.data;
                qrDataInput.value = result.data;
                
                // Auto submit form
                checkinForm.submit();
            },
            {
                returnDetailedScanResult: true,
                preferredCamera: cameraId
            }
        );

        qrScanner.start();
    }

    function stopScanner() {
        if (qrScanner) {
            qrScanner.stop();
            qrScanner.destroy();
            qrScanner = null;
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopScanner);
});
</script>
@endsection
