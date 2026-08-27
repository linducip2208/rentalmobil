<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\RentalOrder;
use App\Models\ReturnRecord;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnProcessingService
{
    public function __construct(
        protected PricingCalculator $pricing,
        protected DamageCalculator $damageCalculator,
        protected InvoiceGenerationService $invoiceService,
        protected NotificationDispatcher $notification,
    ) {}

    public function processReturn(RentalOrder $order, array $data): ReturnRecord
    {
        if (! in_array($order->status, ['active', 'overdue'])) {
            throw new \RuntimeException("Cannot process return for order with status '{$order->status}'.");
        }

        $returnDate = Carbon::parse($data['actual_return_date'] ?? now());
        $vehicle = $order->vehicle;

        return DB::transaction(function () use ($order, $data, $returnDate, $vehicle) {
            $returnRecord = ReturnRecord::create([
                'rental_order_id' => $order->id,
                'actual_return_date' => $returnDate->toDateString(),
                'actual_return_time' => $data['actual_return_time'] ?? now()->format('H:i:s'),
                'return_location_id' => $data['return_location_id'] ?? $order->location_id,
                'return_km' => $data['return_km'] ?? $vehicle->current_km,
                'return_fuel_level' => $data['return_fuel_level'] ?? 100,
                'body_condition' => $data['body_condition'] ?? 'good',
                'interior_condition' => $data['interior_condition'] ?? 'good',
                'tire_condition' => $data['tire_condition'] ?? 'good',
                'has_damage' => $data['has_damage'] ?? false,
                'damage_description' => $data['damage_description'] ?? null,
                'photos' => $data['photos'] ?? [],
                'other_charges' => $data['other_charges'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending_review',
                'inspector_id' => $data['inspector_id'] ?? auth()->id(),
            ]);

            if (! empty($data['damage_reports'])) {
                foreach ($data['damage_reports'] as $damage) {
                    $estimatedCost = $this->damageCalculator->calculateCost(
                        $damage['damage_type'],
                        $damage['severity']
                    );

                    DamageReport::create([
                        'return_record_id' => $returnRecord->id,
                        'rental_order_id' => $order->id,
                        'vehicle_id' => $vehicle->id,
                        'customer_id' => $order->customer_id,
                        'reported_by' => auth()->id(),
                        'damage_type' => $damage['damage_type'],
                        'location_on_vehicle' => $damage['location_on_vehicle'] ?? $damage['damage_location'] ?? null,
                        'severity' => $damage['severity'],
                        'description' => $damage['description'] ?? null,
                        'estimated_cost' => $estimatedCost,
                        'photos' => $damage['photos'] ?? [],
                        'status' => 'reported',
                    ]);
                }
            }

            $charges = $this->calculateReturnCharges($returnRecord);

            $returnRecord->update([
                'late_minutes' => $charges['late_minutes'],
                'late_charge' => $charges['late_fee'],
                'fuel_charge' => $charges['fuel_charge'],
                'damage_total' => $charges['damage_charge'],
                'total_charges' => $charges['total_charges'],
            ]);

            $vehicle->update([
                'current_km' => $returnRecord->return_km,
                'status' => 'maintenance',
            ]);

            return $returnRecord;
        });
    }

    public function calculateReturnCharges(ReturnRecord $returnRecord): array
    {
        $order = $returnRecord->rentalOrder()->with('vehicle')->first();
        $vehicle = $order->vehicle;

        $lateMinutes = 0;
        $lateFee = 0.0;
        $returnAt = Carbon::parse($returnRecord->actual_return_date->format('Y-m-d').' '.($returnRecord->actual_return_time ?? '23:59:59'));
        $lateDeadline = $order->end_date->copy()->endOfDay()->addHours(
            (int) SystemSetting::get('return_grace_hours', 0)
        );

        if ($returnAt->gt($lateDeadline)) {
            $lateMinutes = (int) $lateDeadline->diffInMinutes($returnAt);
            $lateFee = $this->pricing->calculateLateFee($order, $returnAt);
        }

        $fuelCharge = 0.0;
        $fullFuelCost = (float) SystemSetting::get('full_fuel_cost', 150000);

        $missingPercent = max(0, min(100, 100 - (int) $returnRecord->return_fuel_level)) / 100;
        $fuelCharge = round($missingPercent * $fullFuelCost, 2);

        $damageCharge = 0.0;
        if ($returnRecord->has_damage) {
            $damageCharge = DamageReport::where('return_record_id', $returnRecord->id)
                ->sum('estimated_cost');
        }

        $otherCharges = 0.0;
        if ((float) $returnRecord->other_charges > 0) {
            $otherCharges = (float) $returnRecord->other_charges;
        }

        return [
            'late_minutes' => $lateMinutes,
            'late_fee' => round($lateFee, 2),
            'fuel_charge' => $fuelCharge,
            'damage_charge' => round((float) $damageCharge, 2),
            'other_charges' => $otherCharges,
            'total_charges' => round($lateFee + $fuelCharge + $damageCharge + $otherCharges, 2),
        ];
    }

    public function approveReturn(ReturnRecord $returnRecord, int $userId): ReturnRecord
    {
        if ($returnRecord->status !== 'pending_review') {
            throw new \RuntimeException("Cannot approve return with status '{$returnRecord->status}'.");
        }

        return DB::transaction(function () use ($returnRecord, $userId) {
            $returnRecord->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            $order = $returnRecord->rentalOrder;

            $order->update([
                'actual_return_date' => $returnRecord->actual_return_date,
                'late_fee' => $returnRecord->late_charge,
                'fuel_charge' => $returnRecord->fuel_charge,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $totalCharges = (float) $returnRecord->total_charges;

            if ($totalCharges > 0) {
                $order->update([
                    'damage_fee' => DamageReport::where('return_record_id', $returnRecord->id)
                        ->sum('estimated_cost'),
                ]);

                $this->invoiceService->generateAdditionalCharge(
                    $order,
                    'Return charges (late fee, fuel, damage)',
                    $totalCharges
                );
            }

            $depositRefund = $this->pricing->calculateDepositRefund(
                (float) $order->deposit_amount,
                DamageReport::where('return_record_id', $returnRecord->id)->sum('estimated_cost'),
                (float) $returnRecord->late_charge,
                (float) $returnRecord->fuel_charge + (float) $returnRecord->other_charges,
                0.0
            );

            if ($depositRefund > 0) {
                $this->invoiceService->generateRefund($order, $depositRefund);
            } elseif ($depositRefund <= 0) {
                $this->invoiceService->generateAdditionalCharge(
                    $order,
                    'Deposit deduction',
                    (float) $order->deposit_amount
                );
            }

            Vehicle::where('id', $order->vehicle_id)
                ->update(['status' => $returnRecord->has_damage ? 'maintenance' : 'available']);

            $order->customer->increment('total_orders');
            $order->customer->increment('total_spent', (float) $order->amount_paid);
            app(LoyaltyService::class)->updateTier($order->customer);
            $trustChange = $returnRecord->has_damage ? -10 : ((int) $returnRecord->late_minutes > 0 ? -2 : 5);
            app(TrustScoreService::class)->updateScore(
                $order->customer_id,
                $trustChange,
                $returnRecord->has_damage ? 'Pengembalian dengan kerusakan' : ((int) $returnRecord->late_minutes > 0 ? 'Pengembalian terlambat' : 'Pengembalian tepat waktu tanpa kerusakan'),
                ReturnRecord::class,
                $returnRecord->id,
            );

            return $returnRecord->fresh();
        });
    }

    public function disputeReturn(ReturnRecord $returnRecord, string $notes): ReturnRecord
    {
        if ($returnRecord->status !== 'pending_review') {
            throw new \RuntimeException("Cannot dispute return with status '{$returnRecord->status}'.");
        }

        $returnRecord->update([
            'status' => 'disputed',
            'rejection_reason' => $notes,
        ]);

        $order = $returnRecord->rentalOrder;

        $this->notification->dispatch(
            'return_disputed',
            $order->customer,
            [
                'order_number' => $order->order_number,
                'reason' => $notes,
            ]
        );

        Log::info("Return record #{$returnRecord->id} disputed for order {$order->order_number}", [
            'reason' => $notes,
            'inspector_id' => auth()->id(),
        ]);

        return $returnRecord->fresh();
    }
}
