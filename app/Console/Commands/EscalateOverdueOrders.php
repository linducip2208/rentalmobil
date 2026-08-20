<?php

namespace App\Console\Commands;

use App\Models\InvestigationCase;
use App\Models\PoliceReport;
use App\Models\RentalOrder;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EscalateOverdueOrders extends Command
{
    protected $signature = 'orders:escalate-overdue';

    protected $description = 'Escalate overdue rental orders through escalation stages';

    public function handle(): int
    {
        $this->info('Checking for overdue orders...');

        $activeOrders = RentalOrder::where('status', 'active')
            ->where('end_date', '<', now())
            ->with(['customer', 'vehicle', 'driver', 'location'])
            ->get();

        if ($activeOrders->isEmpty()) {
            $this->info('No overdue orders found.');
            return Command::SUCCESS;
        }

        $overdueCount = 0;
        $missingCount = 0;
        $policeCount = 0;

        foreach ($activeOrders as $order) {
            $hoursSinceOverdue = now()->diffInHours($order->end_date);

            if ($hoursSinceOverdue > 168) {
                $this->escalateToPoliceReport($order);
                $policeCount++;
            } elseif ($hoursSinceOverdue > 72) {
                $this->escalateToMissing($order);
                $missingCount++;
            } elseif ($hoursSinceOverdue > 24) {
                $this->escalateToOverdue($order);
                $overdueCount++;
            }
        }

        $this->info("Escalation complete:");
        $this->info("  - Overdue (>24h): {$overdueCount}");
        $this->info("  - Missing (>72h): {$missingCount}");
        $this->info("  - Police report (>168h): {$policeCount}");

        Log::info('Overdue escalation completed', [
            'overdue' => $overdueCount,
            'missing' => $missingCount,
            'police_report' => $policeCount,
        ]);

        return Command::SUCCESS;
    }

    protected function escalateToOverdue(RentalOrder $order): void
    {
        if ($order->status === 'overdue') {
            return;
        }

        $order->update(['status' => 'overdue']);

        $hoursLate = now()->diffInHours($order->end_date);
        $estimatedFee = round($hoursLate * (float) $order->vehicle->late_fee_per_hour, 2);

        $dispatcher = app(NotificationDispatcher::class);
        $dispatcher->dispatch('overdue_notice', $order->customer, [
            'order_number' => $order->order_number,
            'vehicle_name' => $order->vehicle->name,
            'end_date' => $order->end_date->format('d M Y H:i'),
            'hours_late' => $hoursLate,
            'late_fee_per_hour' => $order->vehicle->late_fee_per_hour,
            'estimated_fee' => $estimatedFee,
        ]);

        $this->warn("  ORDER {$order->order_number} → OVERDUK ({$hoursLate}h late, est. fee: Rp {$estimatedFee})");

        Log::warning("Order {$order->order_number} escalated to overdue", [
            'order_id' => $order->id,
            'hours_late' => $hoursLate,
            'estimated_fee' => $estimatedFee,
        ]);
    }

    protected function escalateToMissing(RentalOrder $order): void
    {
        $existingCase = InvestigationCase::where('rental_order_id', $order->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->first();

        if ($existingCase) {
            return;
        }

        $hoursOverdue = now()->diffInHours($order->end_date);

        $case = InvestigationCase::create([
            'vehicle_id' => $order->vehicle_id,
            'customer_id' => $order->customer_id,
            'rental_order_id' => $order->id,
            'type' => 'missing_vehicle',
            'priority' => 'high',
            'status' => 'open',
            'title' => "Missing Vehicle - {$order->vehicle->name}",
            'description' => "Order {$order->order_number} is {$hoursOverdue} hours overdue. Vehicle not returned. Customer: {$order->customer->name} ({$order->customer->phone}).",
            'opened_at' => now(),
        ]);

        $dispatcher = app(NotificationDispatcher::class);
        $dispatcher->dispatch('missing_vehicle_alert', $order->customer, [
            'order_number' => $order->order_number,
            'vehicle_name' => $order->vehicle->name,
            'hours_overdue' => $hoursOverdue,
            'case_number' => $case->case_number,
        ]);

        $this->error("  ORDER {$order->order_number} → MISSING (case: {$case->case_number})");

        Log::critical("Order {$order->order_number} escalated to missing vehicle", [
            'order_id' => $order->id,
            'vehicle_id' => $order->vehicle_id,
            'case_number' => $case->case_number,
        ]);
    }

    protected function escalateToPoliceReport(RentalOrder $order): void
    {
        $case = InvestigationCase::where('rental_order_id', $order->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->first();

        if (!$case) {
            $this->escalateToMissing($order);
            $case = InvestigationCase::where('rental_order_id', $order->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->first();
        }

        if (!$case) {
            return;
        }

        $existingReport = PoliceReport::where('investigation_case_id', $case->id)->first();
        if ($existingReport) {
            return;
        }

        $reportNumber = 'PR-' . now()->format('ymd') . '-' . str_pad(
            PoliceReport::whereDate('created_at', now()->toDateString())->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        $vehicle = $order->vehicle;
        $customer = $order->customer;

        PoliceReport::create([
            'investigation_case_id' => $case->id,
            'vehicle_id' => $order->vehicle_id,
            'rental_order_id' => $order->id,
            'report_number' => $reportNumber,
            'report_date' => now()->toDateString(),
            'incident_date' => $order->end_date,
            'incident_location' => $order->location->address ?? 'Unknown',
            'description' => implode("\n", [
                "LAPORAN KEHILANGAN KENDARAAN",
                "===========================",
                "",
                "Nomor Kasus: {$case->case_number}",
                "Nomor Pesanan: {$order->order_number}",
                "",
                "DATA KENDARAAN:",
                "- Merk/Type: {$vehicle->name}",
                "- Tahun: {$vehicle->year}",
                "- Warna: {$vehicle->color}",
                "- Plat Nomor: {$vehicle->license_plate}",
                "",
                "DATA PEMESAN:",
                "- Nama: {$customer->name}",
                "- No. KTP: {$customer->id_card_number}",
                "- Alamat: {$customer->address}",
                "- No. Telp: {$customer->phone}",
                "",
                "KETERANGAN:",
                "Kendaraan tidak dikembalikan melewati batas waktu yang telah ditentukan.",
                "Customer tidak dapat dihubungi.",
            ]),
            'status' => 'pending',
            'notes' => "Auto-generated from investigation case {$case->case_number} (7+ days overdue)",
        ]);

        $case->update(['status' => 'in_progress']);

        $this->error("  ORDER {$order->order_number} → POLICE REPORT ({$reportNumber})");

        Log::critical("Police report generated for order {$order->order_number}", [
            'report_number' => $reportNumber,
            'case_number' => $case->case_number,
        ]);
    }
}
