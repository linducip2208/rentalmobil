<?php

namespace App\Console\Commands;

use App\Models\NotificationQueue;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:send-pending';

    protected $description = 'Process and send pending notifications from the queue';

    public function handle(): int
    {
        $dispatcher = app(NotificationDispatcher::class);

        $pending = NotificationQueue::pending()
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(100)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending notifications to send.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$pending->count()} pending notifications...");

        $sent = 0;
        $failed = 0;

        foreach ($pending as $notification) {
            try {
                $dispatcher->processQueue(1);
                $sent++;
            } catch (\Exception $e) {
                Log::error("Failed to send notification #{$notification->id}: {$e->getMessage()}");
                $notification->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'retry_count' => $notification->retry_count + 1,
                ]);
                $failed++;
            }
        }

        $retryable = NotificationQueue::retryable()->count();
        if ($retryable > 0) {
            $retried = $dispatcher->retryFailed();
            $this->info("Retried {$retried} failed notifications.");
        }

        $this->info("Notification processing complete:");
        $this->info("  - Sent: {$sent}");
        $this->info("  - Failed: {$failed}");

        Log::info('Pending notifications processed', [
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return Command::SUCCESS;
    }
}
