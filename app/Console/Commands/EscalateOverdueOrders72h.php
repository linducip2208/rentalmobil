<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\InvestigationCase;
use App\Models\RentalOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EscalateOverdueOrders72h extends Command
{
    protected $signature = 'rental:escalate-overdue';

    protected $description = 'Escalate rental orders overdue more than 72 hours to disputed status with investigation case';

    public function handle(): int
    {
        $orders = RentalOrder::where('status', 'overdue')
            ->where('end_date', '<', now()->subHours(72))
            ->whereNull('actual_return_date')
            ->with(['customer', 'vehicle'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders eligible for escalation.');

            return Command::SUCCESS;
        }

        $escalatedCount = 0;

        foreach ($orders as $order) {
            $existingCase = InvestigationCase::where('rental_order_id', $order->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->exists();

            if ($existingCase) {
                $this->line("  {$order->order_number} — already has open investigation, skipping.");

                continue;
            }

            $hoursOverdue = (int) now()->diffInHours($order->end_date);

            $case = InvestigationCase::create([
                'vehicle_id' => $order->vehicle_id,
                'customer_id' => $order->customer_id,
                'rental_order_id' => $order->id,
                'priority' => 'high',
                'status' => 'open',
                'title' => "Overdue 72h+ — {$order->vehicle->name}",
                'description' => "Order {$order->order_number} is {$hoursOverdue} hours overdue. Vehicle not returned. Customer: {$order->customer->name} ({$order->customer->phone}).",
            ]);

            $oldStatus = $order->status;
            $order->update(['status' => 'disputed']);

            AuditLog::create([
                'action' => 'escalation',
                'auditable_type' => RentalOrder::class,
                'auditable_id' => $order->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'disputed', 'case_number' => $case->case_number],
            ]);

            $escalatedCount++;
            $this->warn("  {$order->order_number} → DISPUTED (case: {$case->case_number})");
        }

        $this->info("Escalated {$escalatedCount} order(s).");
        Log::info('EscalateOverdueOrders72h: escalated '.$escalatedCount.' orders');

        return Command::SUCCESS;
    }
}
