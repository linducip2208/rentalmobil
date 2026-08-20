<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\RentalOrder;
use App\Services\PricingEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireOverdueOrders extends Command
{
    protected $signature = 'rental:expire-overdue';

    protected $description = 'Mark active rental orders past their end_date as overdue and calculate late fees';

    public function handle(PricingEngine $pricingEngine): int
    {
        $orders = RentalOrder::where('status', 'active')
            ->whereNull('actual_return_date')
            ->where('end_date', '<', now())
            ->with('vehicle')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No overdue orders found.');
            return Command::SUCCESS;
        }

        $expiredCount = 0;

        foreach ($orders as $order) {
            $lateMinutes = (int) now()->diffInMinutes($order->end_date, false) * -1;
            $lateFee = $pricingEngine->calculateLateFee($order->vehicle, $lateMinutes);

            $oldStatus = $order->status;
            $order->update([
                'status' => 'overdue',
                'late_fee' => $lateFee,
            ]);

            AuditLog::create([
                'action' => 'status_change',
                'auditable_type' => RentalOrder::class,
                'auditable_id' => $order->id,
                'old_values' => ['status' => $oldStatus, 'late_fee' => $order->getOriginal('late_fee')],
                'new_values' => ['status' => 'overdue', 'late_fee' => $lateFee],
            ]);

            $expiredCount++;
            $this->line("  {$order->order_number} → overdue (late fee: Rp " . number_format($lateFee, 0, ',', '.') . ")");
        }

        $this->info("Expired {$expiredCount} overdue order(s).");
        Log::info('ExpireOverdueOrders: expired ' . $expiredCount . ' orders');

        return Command::SUCCESS;
    }
}
