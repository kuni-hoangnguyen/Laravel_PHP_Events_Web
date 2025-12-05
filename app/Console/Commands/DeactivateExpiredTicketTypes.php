<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredTicketTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket-types:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động đổi is_active thành false cho các ticket types của sự kiện đã kết thúc';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra các sự kiện đã kết thúc...');

        try {
            $expiredEvents = Event::where('end_time', '<', now())
                ->whereHas('ticketTypes', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('ticketTypes')
                ->get();

            if ($expiredEvents->isEmpty()) {
                $this->info('Không có sự kiện nào đã kết thúc cần cập nhật.');

                return Command::SUCCESS;
            }

            $totalDeactivated = 0;

            foreach ($expiredEvents as $event) {
                $deactivatedCount = TicketType::where('event_id', $event->event_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                $totalDeactivated += $deactivatedCount;

                $this->info("Đã deactivate {$deactivatedCount} ticket types cho sự kiện: {$event->title} (ID: {$event->event_id})");

                Log::info('Deactivated ticket types for expired event', [
                    'event_id' => $event->event_id,
                    'event_title' => $event->title,
                    'end_time' => $event->end_time,
                    'deactivated_count' => $deactivatedCount,
                ]);
            }

            $this->info("Hoàn thành! Đã deactivate tổng cộng {$totalDeactivated} ticket types.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Lỗi khi deactivate ticket types: '.$e->getMessage());
            Log::error('Error deactivating expired ticket types', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
