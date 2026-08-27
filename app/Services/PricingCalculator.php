<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\PromoVoucher;
use App\Models\RentalOrder;
use App\Models\RentalOrderItem;
use App\Models\SurgePricingRule;
use App\Models\Vehicle;
use Carbon\Carbon;

class PricingCalculator
{
    public function calculateDailyRate(Vehicle $vehicle, Carbon $startDate, Carbon $endDate): float
    {
        $days = max(1, $startDate->diffInDays($endDate));

        if ($days >= 30 && $vehicle->monthly_rate) {
            return (float) $vehicle->monthly_rate;
        }

        if ($days >= 7 && $vehicle->weekly_rate) {
            $fullWeeks = intdiv($days, 7);
            $remainingDays = $days % 7;

            return ($fullWeeks * (float) $vehicle->weekly_rate)
                + ($remainingDays * (float) $vehicle->daily_rate);
        }

        $totalRate = 0.0;
        $current = $startDate->copy();

        while ($current->lt($endDate)) {
            $surge = $this->calculateSurgeMultiplier($vehicle, $current);
            $totalRate += (float) $vehicle->daily_rate * $surge;
            $current->addDay();
        }

        return $totalRate;
    }

    public function calculateSurgeMultiplier(Vehicle $vehicle, Carbon $date): float
    {
        $rules = SurgePricingRule::active()
            ->forDate($date)
            ->where(function ($q) use ($vehicle) {
                $q->whereNull('category_id')
                    ->orWhere('category_id', $vehicle->category_id);
            })
            ->where(function ($q) use ($vehicle) {
                $q->whereNull('location_id')
                    ->orWhere('location_id', $vehicle->location_id);
            })
            ->orderByDesc('priority')
            ->get();

        $multiplier = 1.0;

        foreach ($rules as $rule) {
            if ($rule->isActiveForDateTime($date)) {
                $multiplier = max($multiplier, (float) $rule->multiplier);
            }
        }

        return $multiplier;
    }

    public function calculateAddonTotal(RentalOrder $order, ?array $addonIds = null): float
    {
        $query = RentalOrderItem::where('rental_order_id', $order->id)
            ->whereNotNull('addon_id');

        if ($addonIds) {
            $query->whereIn('addon_id', $addonIds);
        }

        return (float) $query->sum('total_price');
    }

    public function calculateTax(float $amount, float $rate = 0.11): float
    {
        return round($amount * $rate, 2);
    }

    public function calculateDiscount(float $subtotal, ?PromoVoucher $voucher): float
    {
        if (! $voucher || ! $voucher->isValid()) {
            return 0.0;
        }

        return $voucher->calculateDiscount($subtotal);
    }

    public function calculateLateFee(RentalOrder $order, Carbon $returnDate): float
    {
        if (! $order->end_date || $returnDate->lte($order->end_date)) {
            return 0.0;
        }

        $lateHours = $order->end_date->diffInHours($returnDate);
        $lateDays = (int) ceil($lateHours / 24);
        $hourlyRate = (float) $order->vehicle->late_fee_per_hour;

        if ($hourlyRate > 0 && $lateHours <= 48) {
            return round($lateHours * $hourlyRate, 2);
        }

        $dailyRate = max($hourlyRate * 24, (float) $order->daily_rate_snapshot * 1.5);

        return round($lateDays * $dailyRate, 2);
    }

    public function calculateDepositRefund(
        float $deposit,
        float $damageFee = 0.0,
        float $lateFee = 0.0,
        float $fuelCharge = 0.0,
        float $otherCharges = 0.0
    ): float {
        $totalDeductions = $damageFee + $lateFee + $fuelCharge + $otherCharges;

        return round(max(0.0, $deposit - $totalDeductions), 2);
    }

    public function getRentalTypeMultiplier(string $type, ?float $driverDailyCost = null): float
    {
        return match ($type) {
            'self_drive' => 1.0,
            'with_driver' => 1.0 + ($driverDailyCost ?? 0.0),
            default => 1.0,
        };
    }

    public function calculateOrderTotal(
        Vehicle $vehicle,
        Carbon $startDate,
        Carbon $endDate,
        string $rentalType = 'self_drive',
        ?float $driverDailyCost = null,
        ?array $addonIds = null,
        ?PromoVoucher $voucher = null,
        float $taxRate = 0.11
    ): array {
        $baseTotal = $this->calculateDailyRate($vehicle, $startDate, $endDate);
        $typeMultiplier = $this->getRentalTypeMultiplier($rentalType, $driverDailyCost);
        $adjustedBase = $baseTotal * $typeMultiplier;

        $days = max(1, $startDate->diffInDays($endDate));
        $addonTotal = 0.0;

        if ($addonIds) {
            $addons = Addon::whereIn('id', $addonIds)->active()->get();
            $addonTotal = (float) $addons->sum('price') * $days;
        }

        $subtotal = $adjustedBase + $addonTotal;
        $discountAmount = $this->calculateDiscount($subtotal, $voucher);
        $afterDiscount = max(0.0, $subtotal - $discountAmount);
        $taxAmount = $this->calculateTax($afterDiscount, $taxRate);
        $total = round($afterDiscount + $taxAmount, 2);

        return [
            'base_daily_rate' => (float) $vehicle->daily_rate,
            'days' => $days,
            'surge_adjusted_base' => round($baseTotal, 2),
            'type_multiplier' => $typeMultiplier,
            'adjusted_base' => round($adjustedBase, 2),
            'addon_total' => round($addonTotal, 2),
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'after_discount' => round($afterDiscount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }
}
