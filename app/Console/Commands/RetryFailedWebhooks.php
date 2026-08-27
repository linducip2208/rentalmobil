<?php

namespace App\Console\Commands;

use App\Services\WebhookDispatchService;
use Illuminate\Console\Command;

class RetryFailedWebhooks extends Command
{
    protected $signature = 'webhooks:retry-failed';

    protected $description = 'Ulangi pengiriman webhook yang gagal (backoff 5 menit x percobaan)';

    public function handle(WebhookDispatchService $service): int
    {
        $retried = $service->retryFailed();

        $this->info("{$retried} delivery diproses ulang.");

        return self::SUCCESS;
    }
}
