<?php

namespace App\Helpers;

class LogHelper
{
    /**
     * Danh sách các key nhạy cảm cần được ẩn
     */
    private static array $sensitiveKeys = [
        'password', 'password_confirmation', 'token', '_token',
        'api_key', 'secret', 'checksum', 'signature',
        'credit_card', 'cvv', 'card_number', 'card_number',
        'authorization', 'cookie', 'session',
        'client_id', 'client_secret', 'access_token', 'refresh_token',
    ];

    /**
     * Sanitize dữ liệu trước khi log
     * Loại bỏ các thông tin nhạy cảm
     *
     * @param array|mixed $data
     * @return array|mixed
     */
    public static function sanitize($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            $isSensitive = false;
            foreach (self::$sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize headers - loại bỏ headers nhạy cảm
     *
     * @param array $headers
     * @return array
     */
    public static function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization', 'cookie', 'x-api-key', 'x-auth-token',
            'x-csrf-token', 'x-session-id',
        ];

        $sanitized = [];

        foreach ($headers as $key => $value) {
            $lowerKey = strtolower((string) $key);
            $isSensitive = false;

            foreach ($sensitiveHeaders as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Chỉ lấy các field cần thiết từ payment info để log
     *
     * @param array|null $paymentInfo
     * @return array
     */
    public static function sanitizePaymentInfo(?array $paymentInfo): array
    {
        if (!$paymentInfo) {
            return [];
        }

        $allowedFields = ['orderCode', 'status', 'amount', 'description'];
        $sanitized = [];

        foreach ($allowedFields as $field) {
            if (isset($paymentInfo[$field])) {
                $sanitized[$field] = $paymentInfo[$field];
            }
        }

        if (isset($paymentInfo['data']) && is_array($paymentInfo['data'])) {
            $sanitized['data'] = [];
            foreach ($allowedFields as $field) {
                if (isset($paymentInfo['data'][$field])) {
                    $sanitized['data'][$field] = $paymentInfo['data'][$field];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize request data cho AdminLog
     * Chỉ giữ lại các field không nhạy cảm
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeForAdminLog(array $data): array
    {
        $sanitized = self::sanitize($data);

        $excludedFields = ['_token', '_method', '_previous', 'password', 'password_confirmation'];
        foreach ($excludedFields as $field) {
            unset($sanitized[$field]);
        }

        return $sanitized;
    }
}

