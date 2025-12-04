<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động deactivate ticket types của các sự kiện đã kết thúc
// Chạy mỗi giờ để đảm bảo ticket types được deactivate ngay sau khi event kết thúc
Schedule::command('ticket-types:deactivate-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Tự động đánh dấu thất bại cho các thanh toán PayOS quá 10 phút chưa thanh toán
// Chạy mỗi 5 phút để đảm bảo thanh toán quá hạn được xử lý kịp thời
Schedule::command('payments:expire-pending-payos')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
