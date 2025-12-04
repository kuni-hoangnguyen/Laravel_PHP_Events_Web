import './bootstrap';
import { toastFunction } from './toast';
import { initTicketTypes } from './ticket-types';
import { previewImage } from './image-preview';
import { initQRScanner } from './qr-scanner';

window.toastFunction = toastFunction;
window.initTicketTypes = initTicketTypes;
window.previewImage = previewImage;
window.initQRScanner = initQRScanner;

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('qr-video') && document.getElementById('cam-qr-result')) {
        initQRScanner();
    }
});
