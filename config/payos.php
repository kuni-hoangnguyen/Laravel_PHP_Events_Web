<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayOS Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho PayOS payment gateway
    | Lấy thông tin từ: https://my.payos.vn
    |
    */

    'client_id' => env('PAYOS_CLIENT_ID', ''),
    'api_key' => env('PAYOS_API_KEY', ''),
    'checksum_key' => env('PAYOS_CHECKSUM_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | PayOS URLs
    |--------------------------------------------------------------------------
    |
    | URLs cho callback và return
    |
    */

    'return_url' => env('PAYOS_RETURN_URL', '/payments/payos/return'),
    'cancel_url' => env('PAYOS_CANCEL_URL', '/payments/payos/cancel'),
    'webhook_url' => env('PAYOS_WEBHOOK_URL', '/payments/payos/webhook'),
];
