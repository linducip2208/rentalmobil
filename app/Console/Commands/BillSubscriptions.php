<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionBillingService;
use Illuminate\Console\Command;

class BillSubscriptions extends Command
{
    protected $signature = 'subscriptions:bill';

    protected $description = 'Terbitkan invoice langganan untuk semua subscription yang jatuh tempo';

    public function handle(SubscriptionBillingService $service): int
    {
        $due = Subscription::active()->where('auto_renew', true)->count();

        $billed = $service->runBilling();

        $this->info("Langganan aktif: {$due}. Invoice diterbitkan: {$billed}.");

        return Command::SUCCESS;
    }
}
