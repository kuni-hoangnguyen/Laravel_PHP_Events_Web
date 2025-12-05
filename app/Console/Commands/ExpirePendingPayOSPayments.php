<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingPayOSPayments extends Command
{
    protected $signature = 'payments:expire-pending-non-cash';

    protected $description = 'Tự động đánh dấu thất bại cho các thanh toán khác tiền mặt quá 10 phút chưa thanh toán';

    public function handle()
    {
        $this->info('Đang kiểm tra các thanh toán khác tiền mặt quá hạn...');

        try {
            $cashMethod = PaymentMethod::where('name', 'Tiền mặt')->first();
            
            if (!$cashMethod) {
                $this->warn('Không tìm thấy payment method Tiền mặt.');
                return Command::SUCCESS;
            }

            $expiredPayments = Payment::with(['ticket.ticketType', 'paymentMethod'])
                ->where('method_id', '!=', $cashMethod->method_id)
                ->where('status', 'failed')
                ->whereNull('paid_at')
                ->where('created_at', '<=', now()->subMinutes(10))
                ->get();

            if ($expiredPayments->isEmpty()) {
                $this->info('Không có thanh toán nào quá hạn.');
                return Command::SUCCESS;
            }

            $expiredCount = 0;

            foreach ($expiredPayments as $payment) {
                DB::beginTransaction();
                try {
                    $ticket = $payment->ticket;
                    
                    if (!$ticket) {
                        $this->warn("Payment ID {$payment->payment_id} không có ticket liên kết, bỏ qua.");
                        DB::rollBack();
                        continue;
                    }
                    
                    $ticketType = $ticket->ticketType;

                    if (!$ticketType) {
                        $this->warn("Ticket ID {$ticket->ticket_id} không có ticket type liên kết, bỏ qua.");
                        DB::rollBack();
                        continue;
                    }

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

                    $methodName = $payment->paymentMethod->name ?? 'Unknown';
                    $this->info("Đã đánh dấu thất bại payment ID: {$payment->payment_id} ({$methodName}), Ticket ID: {$ticket->ticket_id}");

                    Log::info('Non-cash payment expired', [
                        'payment_id' => $payment->payment_id,
                        'ticket_id' => $ticket->ticket_id,
                        'method' => $methodName,
                        'created_at' => $payment->created_at,
                        'expired_at' => now(),
                        'minutes_since_created' => $payment->created_at->diffInMinutes(now()),
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to expire non-cash payment', [
                        'payment_id' => $payment->payment_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Lỗi khi xử lý payment ID: {$payment->payment_id} - {$e->getMessage()}");
                }
            }

            $this->info("Hoàn thành! Đã đánh dấu thất bại {$expiredCount} thanh toán quá hạn.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Lỗi khi kiểm tra thanh toán quá hạn: '.$e->getMessage());
            Log::error('Error expiring pending non-cash payments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
