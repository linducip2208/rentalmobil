<?php

namespace App\Jobs;

use App\Models\GpsCommand;
use App\Services\Gps\GpsCommandService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendGpsCommand implements ShouldQueue
{
    use Queueable;
    public int $tries = 2;

    public function __construct(public int $commandId) { $this->onQueue('gps'); }

    public function handle(GpsCommandService $service): void
    {
        $command = GpsCommand::with('tracker.integration.provider')->find($this->commandId);
        if ($command?->status === 'approved') $service->send($command);
    }
}
