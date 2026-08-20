<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\ServiceSchedule;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReminderNotifications extends Command
{
    protected $signature = 'notifications:send-reminders';

    protected $description = 'Send daily reminder notifications (return, payment, maintenance)';

    public function handle(): int
    {
        $this->info('Sending daily reminders...');
        $dispatcher = app(NotificationDispatcher::class);

        $returnCount = $this->sendReturnReminders($dispatcher);
        $paymentCount = $this->sendPaymentReminders($dispatcher);
        $maintenanceCount = $this->sendMaintenanceReminders($dispatcher);

        $total = $returnCount + $paymentCount + $maintenanceCount;

        $this->info("Reminders sent:");
        $this->info("  - Return reminders (H-1): {$returnCount}");
        $this->info("  - Payment due reminders: {$paymentCount}");
        $this->info("  - Maintenance reminders: {$maintenanceCount}");
        $this->info("  - Total: {$total}");

        Log::info('Daily reminders sent', [
            'return_reminders' => $returnCount,
            'payment_reminders' => $paymentCount,
            'maintenance_reminders' => $maintenanceCount,
        ]);

        return Command::SUCCESS;
    }

    protected function sendReturnReminders(NotificationDispatcher $dispatcher): int
    {
        $orders = RentalOrder::where('status', 'active')
            ->whereDate('end_date', now()->addDay())
            ->with(['customer', 'vehicle'])
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            try {
                $dispatcher->sendReturnReminder($order);
                $count++;
                $this->line("  → Return reminder: {$order->order_number} ({$order->customer->name})");
            } catch (\Exception $e) {
                Log::error("Failed return reminder for order #{$order->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    protected function sendPaymentReminders(NotificationDispatcher $dispatcher): int
    {
        $invoices = Invoice::overdue()
            ->with(['customer', 'rentalOrder'])
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            try {
                $dispatcher->sendPaymentReminder($invoice);
                $count++;
                $this->line("  → Payment reminder: {$invoice->invoice_number} ({$invoice->customer->name})");
            } catch (\Exception $e) {
                Log::error("Failed payment reminder for invoice #{$invoice->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    protected function sendMaintenanceReminders(NotificationDispatcher $dispatcher): int
    {
        $dueSchedules = ServiceSchedule::dueSoon(7)
            ->with('vehicle')
            ->get();

        $count = 0;
        foreach ($dueSchedules as $schedule) {
            try {
                $this->dispatchMaintenanceReminder($dispatcher, $schedule);
                $count++;
                $this->line("  → Maintenance reminder: {$schedule->vehicle->name} ({$schedule->service_type})");
            } catch (\Exception $e) {
                Log::error("Failed maintenance reminder for schedule #{$schedule->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    protected function dispatchMaintenanceReminder(NotificationDispatcher $dispatcher, ServiceSchedule $schedule): void
    {
        $vehicle = $schedule->vehicle;
        $daysUntil = $schedule->next_service_date
            ? now()->diffInDays($schedule->next_service_date, false)
            : null;

        $message = "Reminder: {$vehicle->name} needs {$schedule->service_type}";
        if ($daysUntil !== null) {
            if ($daysUntil < 0) {
                $message .= " (OVERDUE by " . abs($daysUntil) . " days)";
            } else {
                $message .= " in {$daysUntil} days";
            }
        }

        Log::info($message, [
            'vehicle_id' => $vehicle->id,
            'schedule_id' => $schedule->id,
            'service_type' => $schedule->service_type,
            'next_service_date' => $schedule->next_service_date?->format('Y-m-d'),
        ]);
    }
}
