<?php

namespace App\Services;

use App\Models\InvestigationCase;
use App\Models\NotificationQueue;
use App\Models\PoliceReport;
use App\Models\RentalOrder;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OverdueEscalationService
{
    protected int $reminderGraceHours;
    protected int $overdueThresholdHours;
    protected int $missingThresholdHours;

    public function __construct()
    {
        $this->reminderGraceHours = (int) SystemSetting::get('reminder_grace_hours', 2);
        $this->overdueThresholdHours = (int) SystemSetting::get('overdue_threshold_hours', 24);
        $this->missingThresholdHours = (int) SystemSetting::get('missing_threshold_hours', 72);
    }

    public function checkOverdueOrders()
    {
        $activeOrders = RentalOrder::where('status', 'active')
            ->where('end_date', '<', now())
            ->with(['customer', 'vehicle', 'driver'])
            ->get();

        foreach ($activeOrders as $order) {
            $hoursSinceOverdue = now()->diffInHours($order->end_date);

            if ($hoursSinceOverdue >= $this->missingThresholdHours) {
                $this->escalateToMissing($order);
            } elseif ($hoursSinceOverdue >= $this->overdueThresholdHours) {
                $this->escalateToOverdue($order);
            } else {
                $this->sendInitialOverdueReminder($order);
            }
        }

        return $activeOrders->count();
    }

    public function escalateToOverdue(RentalOrder $order): void
    {
        if ($order->status === 'overdue') {
            return;
        }

        $order->update(['status' => 'overdue']);

        $hoursLate = now()->diffInHours($order->end_date);

        $this->notification()->dispatch(
            'overdue_notice',
            $order->customer,
            [
                'order_number' => $order->order_number,
                'vehicle_name' => $order->vehicle->name,
                'end_date' => $order->end_date->format('d M Y H:i'),
                'hours_late' => $hoursLate,
                'late_fee_per_hour' => $order->vehicle->late_fee_per_hour,
                'estimated_fee' => round($hoursLate * (float) $order->vehicle->late_fee_per_hour, 2),
            ]
        );

        if ($order->driver) {
            $this->notification()->dispatch(
                'driver_overdue_notice',
                $order->driver,
                [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name,
                    'vehicle_name' => $order->vehicle->name,
                    'end_date' => $order->end_date->format('d M Y H:i'),
                ]
            );
        }

        Log::warning("Order {$order->order_number} escalated to overdue", [
            'order_id' => $order->id,
            'hours_late' => $hoursLate,
            'customer_id' => $order->customer_id,
        ]);
    }

    public function escalateToMissing(RentalOrder $order): void
    {
        $existingCase = InvestigationCase::where('rental_order_id', $order->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->first();

        if ($existingCase) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'overdue']);

            $case = InvestigationCase::create([
                'vehicle_id' => $order->vehicle_id,
                'customer_id' => $order->customer_id,
                'rental_order_id' => $order->id,
                'type' => 'missing_vehicle',
                'priority' => 'high',
                'status' => 'open',
                'title' => "Missing Vehicle - {$order->vehicle->name}",
                'description' => "Order {$order->order_number} is {$order->end_date->diffInHours(now())} hours overdue. Vehicle has not been returned. Customer: {$order->customer->name} ({$order->customer->phone}).",
                'opened_at' => now(),
            ]);

            $this->notification()->dispatch(
                'missing_vehicle_alert',
                $order->customer,
                [
                    'order_number' => $order->order_number,
                    'vehicle_name' => $order->vehicle->name,
                    'hours_overdue' => $order->end_date->diffInHours(now()),
                    'case_number' => $case->case_number,
                ]
            );

            Log::critical("Order {$order->order_number} escalated to missing vehicle", [
                'order_id' => $order->id,
                'vehicle_id' => $order->vehicle_id,
                'customer_id' => $order->customer_id,
                'case_number' => $case->case_number,
                'hours_overdue' => $order->end_date->diffInHours(now()),
            ]);
        });
    }

    public function generatePoliceReport(InvestigationCase $case): PoliceReport
    {
        $order = $case->rentalOrder()->with(['customer', 'vehicle'])->first();

        if (!$order) {
            throw new \RuntimeException('Cannot generate police report without an associated order.');
        }

        $reportNumber = PoliceReport::whereDate('created_at', now()->toDateString())
            ->count() + 1;

        $report = PoliceReport::create([
            'investigation_case_id' => $case->id,
            'vehicle_id' => $order->vehicle_id,
            'rental_order_id' => $order->id,
            'report_number' => 'PR-' . now()->format('ymd') . '-' . str_pad($reportNumber, 3, '0', STR_PAD_LEFT),
            'report_date' => now()->toDateString(),
            'incident_date' => $order->end_date,
            'incident_location' => $order->location->address ?? 'Unknown',
            'description' => $this->buildPoliceReportDescription($order, $case),
            'status' => 'pending',
            'notes' => "Auto-generated from investigation case {$case->case_number}",
        ]);

        $case->update([
            'status' => 'in_progress',
        ]);

        Log::info("Police report generated for case {$case->case_number}", [
            'report_number' => $report->report_number,
            'vehicle_id' => $order->vehicle_id,
            'customer_id' => $order->customer_id,
        ]);

        return $report;
    }

    protected function sendInitialOverdueReminder(RentalOrder $order): void
    {
        $hoursSinceEnd = now()->diffInHours($order->end_date);

        if ($hoursSinceEnd < $this->reminderGraceHours) {
            return;
        }

        $reminderCount = NotificationQueue::where('notifiable_type', Customer::class)
            ->where('notifiable_id', $order->customer_id)
            ->where('subject', 'like', "%{$order->order_number}%")
            ->where('created_at', '>=', $order->end_date)
            ->count();

        if ($reminderCount >= 2) {
            return;
        }

        $this->notification()->dispatch(
            'return_reminder',
            $order->customer,
            [
                'order_number' => $order->order_number,
                'vehicle_name' => $order->vehicle->name,
                'end_date' => $order->end_date->format('d M Y H:i'),
                'hours_overdue' => $hoursSinceEnd,
                'late_fee_per_hour' => $order->vehicle->late_fee_per_hour,
            ]
        );
    }

    protected function buildPoliceReportDescription(RentalOrder $order, InvestigationCase $case): string
    {
        $customer = $order->customer;
        $vehicle = $order->vehicle;

        return implode("\n", [
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
            "- No. Rangka: -",
            "- No. Mesin: -",
            "",
            "DATA PEMESAN:",
            "- Nama: {$customer->name}",
            "- No. KTP: {$customer->id_card_number}",
            "- Alamat: {$customer->address}",
            "- No. Telp: {$customer->phone}",
            "- Kontak Darurat: {$customer->emergency_contact_name} ({$customer->emergency_contact_phone})",
            "",
            "DATA PERJALANAN:",
            "- Tanggal Sewa: {$order->start_date->format('d/m/Y H:i')}",
            "- Tanggal Pengembalian: {$order->end_date->format('d/m/Y H:i')}",
            "- Lokasi Pickup: " . ($order->location->name ?? 'N/A'),
            "- Estimasi Odometer: {$vehicle->current_km} km",
            "",
            "KETERANGAN:",
            "Kendaraan tidak dikembalikan melewati batas waktu yang telah ditentukan.",
            "Customer tidak dapat dihubungi sejak " . now()->subDays(2)->format('d/m/Y') . ".",
        ]);
    }

    protected function notification(): NotificationDispatcher
    {
        return app(NotificationDispatcher::class);
    }
}
