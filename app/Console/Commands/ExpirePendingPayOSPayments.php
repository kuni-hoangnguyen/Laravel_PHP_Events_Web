<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingPayOSPayments extends Command
{
    protected $signature = 'payments:expire-pending-payos';

    protected $description = 'Tự động đánh dấu thất bại cho các thanh toán PayOS quá 10 phút chưa thanh toán';

    public function handle()
    {
        $this->info('Đang kiểm tra các thanh toán PayOS quá hạn...');

        try {
            $payOSMethod = PaymentMethod::where('name', 'PayOS')->first();
            
            if (!$payOSMethod) {
                $this->warn('Không tìm thấy payment method PayOS.');
                return Command::SUCCESS;
            }

            $expiredPayments = Payment::with(['ticket.ticketType'])
                ->where('method_id', $payOSMethod->method_id)
                ->where('status', 'failed')
                ->whereNull('paid_at')
                ->where('created_at', '<=', now()->subMinutes(10))
                ->get();

            if ($expiredPayments->isEmpty()) {
                $this->info('Không có thanh toán PayOS nào quá hạn.');
                return Command::SUCCESS;
            }

            $expiredCount = 0;

            foreach ($expiredPayments as $payment) {
                DB::beginTransaction();
                try {
                    $ticket = $payment->ticket;
                    $ticketType = $ticket->ticketType;

                    $payment->update([
                        'status' => 'failed',
                    ]);

                    if ($ticket->payment_status === 'pending') {
                        $ticket->update([
                            'payment_status' => 'cancelled',
                        ]);

                        $ticketType->increment('remaining_quantity', $ticket->quantity ?? 1);
                    }

                    DB::commit();
                    $expiredCount++;

                    $this->info("Đã đánh dấu thất bại payment ID: {$payment->payment_id}, Ticket ID: {$ticket->ticket_id}");

                    Log::info('PayOS payment expired', [
                        'payment_id' => $payment->payment_id,
                        'ticket_id' => $ticket->ticket_id,
                        'created_at' => $payment->created_at,
                        'expired_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to expire PayOS payment', [
                        'payment_id' => $payment->payment_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Lỗi khi xử lý payment ID: {$payment->payment_id} - {$e->getMessage()}");
                }
            }

            $this->info("Hoàn thành! Đã đánh dấu thất bại {$expiredCount} thanh toán PayOS quá hạn.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Lỗi khi kiểm tra thanh toán quá hạn: '.$e->getMessage());
            Log::error('Error expiring pending PayOS payments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
