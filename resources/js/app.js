import './bootstrap';
import QrScanner from 'qr-scanner';

const video = document.getElementById('qr-video');
const videoContainer = document.getElementById('video-container');
const camList = document.getElementById('cam-list');
const camQrResult = document.getElementById('cam-qr-result');

function setResult(label, result) {
    label.textContent = result.data || result;
    label.style.color = 'teal';
    document.getElementById('qr_data_input').value = result.data || result;
    clearTimeout(label.highlightTimeout);
    label.highlightTimeout = setTimeout(() => label.style.color = 'inherit', 100);
    // Tự động submit form khi quét được mã QR
    const form = document.getElementById('qr-checkin-form');
    if (form) {
        scanner.stop();
        form.submit();
    }
}

// Web Cam Scanning
if (video && camQrResult) {
    const scanner = new QrScanner(video, result => setResult(camQrResult, result), {
        highlightScanRegion: true,
        highlightCodeOutline: true,
    });
    scanner.start();
    QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
        const option = document.createElement('option');
        option.value = camera.id;
        option.text = camera.label;
        camList && camList.add(option);
    }));
    window.scanner = scanner;
}
