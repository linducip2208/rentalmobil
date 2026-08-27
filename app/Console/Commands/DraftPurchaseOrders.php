<?php

namespace App\Console\Commands;

use App\Services\PurchaseOrderService;
use Illuminate\Console\Command;

class DraftPurchaseOrders extends Command
{
    protected $signature = 'maintenance:draft-purchase-orders';

    protected $description = 'Draft PO suku cadang otomatis untuk stok di bawah minimum';

    public function handle(): int
    {
        $results = app(PurchaseOrderService::class)->draftForLowStock();

        $this->info("PO draft dibuat: {$results['created']} untuk {$results['parts']} item low-stock.");

        return self::SUCCESS;
    }
}
