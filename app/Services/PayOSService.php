<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use PayOS\Exceptions\APIException;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\PayOS;

class PayOSService
{
    protected PayOS $payOS;

    public function __construct()
    {
        $clientId = config('payos.client_id');
        $apiKey = config('payos.api_key');
        $checksumKey = config('payos.checksum_key');

        if (empty($clientId) || empty($apiKey) || empty($checksumKey)) {
            Log::warning('PayOS configuration is missing', [
                'has_client_id' => !empty($clientId),
                'has_api_key' => !empty($apiKey),
                'has_checksum_key' => !empty($checksumKey),
            ]);
        }

        $this->payOS = new PayOS(
            $clientId,
            $apiKey,
            $checksumKey
        );
    }

    /**
     * Tạo payment link từ PayOS
     *
     * @param Ticket $ticket
     * @param Payment $payment
     * @param string $returnUrl
     * @param string $cancelUrl
     * @return array
     * @throws \Exception
     */
    public function createPaymentLink(Ticket $ticket, Payment $payment, string $returnUrl, string $cancelUrl): array
    {
        try {
            $event = $ticket->ticketType->event;
            
            $orderCode = (int) (($payment->payment_id * 1000 + time() % 1000) % 999999);
            if ($orderCode < 1) {
                $orderCode = (int) (time() % 999999) + 1;
            }

            $amount = (int) round($payment->amount);

            $eventTitle = $event->title ?? $event->event_name ?? 'Sự kiện';
            $description = mb_substr($eventTitle, 0, 25);
            if (mb_strlen($eventTitle) > 25) {
                $description = mb_substr($eventTitle, 0, 22) . '...';
            }

            $paymentData = new CreatePaymentLinkRequest(
                orderCode: $orderCode,
                amount: $amount,
                description: $description,
                returnUrl: $returnUrl,
                cancelUrl: $cancelUrl
            );

            $result = $this->payOS->paymentRequests->create($paymentData, options: ['asArray' => true]);

            $payment->update([
                'transaction_id' => (string) $result['orderCode'],
            ]);

            return [
                'success' => true,
                'checkout_url' => $result['checkoutUrl'],
                'order_code' => $result['orderCode'],
            ];
        } catch (APIException $e) {
            Log::error('PayOS API error', [
                'error' => $e->getMessage(),
                'status' => $e->status ?? null,
                'error_code' => $e->errorCode ?? null,
                'payment_id' => $payment->payment_id,
                'ticket_id' => $ticket->ticket_id,
            ]);

            throw new \Exception('Lỗi PayOS: ' . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            Log::error('PayOS createPaymentLink error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'payment_id' => $payment->payment_id,
                'ticket_id' => $ticket->ticket_id,
            ]);

            throw $e;
        }
    }

    /**
     * Xác thực webhook từ PayOS
     *
     * @param array $data
     * @return bool
     */
    public function verifyWebhook(array $data): bool
    {
        try {
            if (isset($data['data']) && isset($data['code'])) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('PayOS verifyWebhook error', [
                'error' => $e->getMessage(),
                'data' => LogHelper::sanitize($data),
            ]);
            return false;
        }
    }

    /**
     * Lấy thông tin payment từ PayOS
     *
     * @param int $orderCode
     * @return array|null
     */
    public function getPaymentInfo(int $orderCode): ?array
    {
        try {
            $response = $this->payOS->paymentRequests->get($orderCode, options: ['asArray' => true]);
            
            Log::info('PayOS getPaymentInfo response', [
                'order_code' => $orderCode,
                'response' => LogHelper::sanitizePaymentInfo($response),
            ]);
            
            if (isset($response['data'])) {
                return $response['data'];
            }
            
            if (isset($response['orderCode'])) {
                return $response;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('PayOS getPaymentInfo error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'order_code' => $orderCode,
            ]);
            return null;
        }
    }

    /**
     * Xử lý callback từ PayOS
     *
     * @param array $data
     * @return Payment|null
     */
    public function handleCallback(array $data): ?Payment
    {
        try {
            Log::info('PayOS webhook received', ['data' => LogHelper::sanitize($data)]);

            if (!isset($data['data']) || !isset($data['code'])) {
                Log::warning('PayOS webhook: Invalid data structure', ['data' => LogHelper::sanitize($data)]);
                return null;
            }

            if ($data['code'] !== '00') {
                Log::warning('PayOS webhook: Code is not 00', ['code' => $data['code'], 'desc' => $data['desc'] ?? '']);
                return null;
            }

            $orderCode = $data['data']['orderCode'] ?? null;
            if (!$orderCode) {
                Log::warning('PayOS webhook: Missing orderCode', ['data' => LogHelper::sanitize($data)]);
                return null;
            }

            $payment = Payment::where('transaction_id', (string) $orderCode)->first();
            if (!$payment) {
                Log::warning('PayOS callback: Payment not found', ['order_code' => $orderCode]);
                return null;
            }

            $status = $data['data']['status'] ?? null;
            Log::info('PayOS webhook status', ['order_code' => $orderCode, 'status' => $status, 'payment_id' => $payment->payment_id]);

            if ($status === 'PAID') {
                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    $payment->update([
                        'status' => 'success',
                        'paid_at' => now(),
                    ]);

                    $payment->ticket->update([
                        'payment_status' => 'paid',
                    ]);

                    \Illuminate\Support\Facades\DB::commit();
                    
                    Log::info('PayOS payment updated successfully', [
                        'payment_id' => $payment->payment_id,
                        'ticket_id' => $payment->ticket_id,
                        'order_code' => $orderCode,
                    ]);

                    return $payment;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    Log::error('PayOS update payment failed', [
                        'error' => $e->getMessage(),
                        'payment_id' => $payment->payment_id,
                    ]);
                    throw $e;
                }
            } elseif ($status === 'CANCELLED' || $status === 'EXPIRED') {
                $payment->update([
                    'status' => 'failed',
                ]);

                return $payment;
            }

            return $payment;
        } catch (\Exception $e) {
            Log::error('PayOS handleCallback error', [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'data' => LogHelper::sanitize($data),
            ]);
            return null;
        }
    }

    /**
     * @param Payment $payment
     * @param string|null $reason
     * @return array
     * @throws \Exception
     */
    public function refundPayment(Payment $payment, ?string $reason = null): array
    {
        try {
           
            
            Log::warning('PayOS refund not implemented', [
                'payment_id' => $payment->payment_id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'reason' => $reason,
            ]);

            throw new \Exception('PayOS refund API chưa được implement.');
        } catch (\Exception $e) {
            Log::error('PayOS refund error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->payment_id,
                'transaction_id' => $payment->transaction_id,
            ]);
            throw $e;
        }
    }
}

