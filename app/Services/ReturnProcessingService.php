<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\ReturnRecord;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\DamageCalculator;
use App\Services\InvoiceGenerationService;
use App\Services\PricingCalculator;
use App\Services\NotificationDispatcher;
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
        if (!in_array($order->status, ['active', 'overdue'])) {
            throw new \RuntimeException("Cannot process return for order with status '{$order->status}'.");
        }

        $returnDate = Carbon::parse($data['return_date'] ?? now());
        $vehicle = $order->vehicle;

        return DB::transaction(function () use ($order, $data, $returnDate, $vehicle) {
            $returnRecord = ReturnRecord::create([
                'rental_order_id' => $order->id,
                'return_date' => $returnDate,
                'return_km' => $data['return_km'] ?? $vehicle->current_km,
                'fuel_level' => $data['fuel_level'] ?? 'full',
                'condition_notes' => $data['condition_notes'] ?? null,
                'body_condition' => $data['body_condition'] ?? 'good',
                'interior_condition' => $data['interior_condition'] ?? 'good',
                'tire_condition' => $data['tire_condition'] ?? 'good',
                'has_damage' => $data['has_damage'] ?? false,
                'damage_description' => $data['damage_description'] ?? null,
                'damage_photos' => $data['damage_photos'] ?? [],
                'status' => 'pending',
                'inspector_id' => auth()->id(),
            ]);

            if (!empty($data['damage_reports'])) {
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
                        'damage_location' => $damage['damage_location'] ?? null,
                        'severity' => $damage['severity'],
                        'description' => $damage['description'] ?? null,
                        'estimated_cost' => $estimatedCost,
                        'photos' => $damage['photos'] ?? [],
                        'status' => 'pending',
                    ]);
                }
            }

            $charges = $this->calculateReturnCharges($returnRecord);

            $returnRecord->update([
                'late_minutes' => $charges['late_minutes'],
                'late_fee' => $charges['late_fee'],
                'extra_charge' => $charges['fuel_charge'] + $charges['other_charges'],
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
        $lateDeadline = $order->end_date->copy()->addHours(
            (int) SystemSetting::get('return_grace_hours', 0)
        );

        if ($returnDate = $returnRecord->return_date) {
            if ($returnDate->gt($lateDeadline)) {
                $lateMinutes = (int) $lateDeadline->diffInMinutes($returnDate);
                $lateFee = $this->pricing->calculateLateFee($order, $returnDate);
            }
        }

        $fuelCharge = 0.0;
        $fullFuelCost = (float) SystemSetting::get('full_fuel_cost', 150000);

        $fuelLevels = [
            'empty' => 1.0,
            'quarter' => 0.75,
            'half' => 0.5,
            'three_quarter' => 0.25,
            'full' => 0.0,
        ];

        $missingPercent = $fuelLevels[$returnRecord->fuel_level] ?? 0.0;
        $fuelCharge = round($missingPercent * $fullFuelCost, 2);

        $damageCharge = 0.0;
        if ($returnRecord->has_damage) {
            $damageCharge = DamageReport::where('return_record_id', $returnRecord->id)
                ->sum('estimated_cost');
        }

        $otherCharges = 0.0;
        if ((float) $returnRecord->extra_charge > 0) {
            $otherCharges = (float) $returnRecord->extra_charge;
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
        if ($returnRecord->status !== 'pending') {
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
                'actual_return_date' => $returnRecord->return_date,
                'late_fee' => $returnRecord->late_fee,
                'status' => 'completed',
            ]);

            $totalCharges = (float) $returnRecord->late_fee
                + (float) $returnRecord->extra_charge
                + DamageReport::where('return_record_id', $returnRecord->id)
                    ->sum('estimated_cost');

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
                (float) $returnRecord->late_fee,
                (float) $returnRecord->extra_charge,
                0.0
            );

            if ($depositRefund > 0 && $depositRefund < (float) $order->deposit_amount) {
                $deduction = (float) $order->deposit_amount - $depositRefund;
                $this->invoiceService->generateRefund($order, $depositRefund);
            } elseif ($depositRefund <= 0) {
                $this->invoiceService->generateAdditionalCharge(
                    $order,
                    'Deposit deduction',
                    (float) $order->deposit_amount
                );
            }

            Vehicle::where('id', $order->vehicle_id)
                ->update(['status' => 'available']);

            return $returnRecord->fresh();
        });
    }

    public function disputeReturn(ReturnRecord $returnRecord, string $notes): ReturnRecord
    {
        if ($returnRecord->status !== 'pending') {
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
