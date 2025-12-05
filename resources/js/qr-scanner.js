import QrScanner from 'qr-scanner';

let scanner = null;

export function initQRScanner() {
    const video = document.getElementById('qr-video');
    const camList = document.getElementById('cam-list');
    const qrResult = document.getElementById('cam-qr-result');
    const qrDataInput = document.getElementById('qr_data_input');
    const checkinForm = document.getElementById('qr-checkin-form');

    if (!video || !qrResult) {
        return;
    }

    function startScanner(cameraId) {
        stopScanner();

        scanner = new QrScanner(
            video,
            result => {
                qrResult.textContent = result.data;
                if (qrDataInput) {
                    qrDataInput.value = result.data;
                }

                if (checkinForm) {
                    checkinForm.submit();
                }
            },
            {
                returnDetailedScanResult: true,
                preferredCamera: cameraId,
                highlightScanRegion: true,
                highlightCodeOutline: true,
            }
        );

        scanner.start();
    }

    function stopScanner() {
        if (scanner) {
            scanner.stop();
            scanner.destroy();
            scanner = null;
        }
    }

    if (camList) {
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

            if (cameras.length > 0) {
                camList.value = cameras[0].id;
                startScanner(cameras[0].id);
            }
        }).catch(err => {
            console.error('Error loading cameras:', err);
            camList.innerHTML = '<option value="">Lỗi tải camera</option>';
        });

        camList.addEventListener('change', function () {
            if (this.value) {
                startScanner(this.value);
            } else {
                stopScanner();
            }
        });
    } else {
        scanner = new QrScanner(video, result => {
            qrResult.textContent = result.data;
            if (qrDataInput) {
                qrDataInput.value = result.data;
            }

            if (checkinForm) {
                checkinForm.submit();
            }
        }, {
            highlightScanRegion: true,
            highlightCodeOutline: true,
        });

        scanner.start();
    }

    window.addEventListener('beforeunload', stopScanner);
    window.scanner = scanner;
}

export function stopQRScanner() {
    if (scanner) {
        scanner.stop();
        scanner.destroy();
        scanner = null;
    }
}

export function startQRScanner() {
    if (scanner && !scanner._active) {
        scanner.start();
    }
}

