<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecoverAbandonedBookings extends Command
{
    protected $signature = 'marketing:recover-abandoned';

    protected $description = 'Kirim reminder booking terbengkalai & expire lead lama';

    public function handle(): int
    {
$results = app(\App\Services\AbandonedBookingRecoveryService::class)->sendReminders();

$this->info("Reminder dikirim: {$results['reminders_sent']}, lead expired: {$results['expired']}.");
return self::SUCCESS;
    }
}
