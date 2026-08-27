<?php

namespace App\Console\Commands;

use App\Services\AbandonedBookingRecoveryService;
use Illuminate\Console\Command;

class RecoverAbandonedBookings extends Command
{
    protected $signature = 'marketing:recover-abandoned';

    protected $description = 'Kirim reminder booking terbengkalai & expire lead lama';

    public function handle(): int
    {
        $results = app(AbandonedBookingRecoveryService::class)->sendReminders();

        $this->info("Reminder dikirim: {$results['reminders_sent']}, lead expired: {$results['expired']}.");

        return self::SUCCESS;
    }
}
