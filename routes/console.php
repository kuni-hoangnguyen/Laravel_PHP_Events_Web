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
