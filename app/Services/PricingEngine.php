<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\PromoVoucher;
use App\Models\SystemSetting;
use App\Models\SurgePricingRule;
use App\Models\Vehicle;
use Carbon\Carbon;

class PricingEngine
{
    /**
     * Calculate the full rental pricing breakdown.
     */
    public function calculateRentalPrice(
        Vehicle $vehicle,
        string $startDate,
        string $endDate,
        ?string $rentalType = 'self_drive',
        ?array $addonIds = null,
        ?string $promoCode = null
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $durationDays = max(1, $start->diffInDays($end));

        $dailyRate = (float) $vehicle->daily_rate;
        $surgeMultiplier = 1.0;
        $surgeBreakdown = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            $surge = $this->getSurgePricing($vehicle, $current->toDateString());
            if ($surge) {
                $multiplier = (float) $surge->multiplier;
                $surgeBreakdown[] = [
                    'date' => $current->toDateString(),
                    'rule' => $surge->name,
                    'multiplier' => $multiplier,
                ];
                $surgeMultiplier = max($surgeMultiplier, $multiplier);
            }
            $current->addDay();
        }

        $effectiveDailyRate = round($dailyRate * $surgeMultiplier, 2);

        $baseTotal = round($effectiveDailyRate * $durationDays, 2);

        $driverFeePerDay = $this->getDriverFee(1, $rentalType);
        $rentalTypeMultiplier = $this->getRentalTypeMultiplier($rentalType);
        if ($rentalType === 'with_driver') {
            $baseTotal += round($driverFeePerDay * $durationDays, 2);
        } elseif ($rentalType === 'airport_transfer') {
            $baseTotal = round($baseTotal * $rentalTypeMultiplier, 2);
        } elseif ($rentalType === 'corporate') {
            $baseTotal = round($baseTotal * $rentalTypeMultiplier, 2);
        }

        $addonTotal = 0.0;
        $addonDetails = [];
        if (!empty($addonIds)) {
            $addons = Addon::whereIn('id', $addonIds)->active()->get();
            foreach ($addons as $addon) {
                $price = match ($addon->price_type) {
                    'per_day' => (float) $addon->price * $durationDays,
                    default => (float) $addon->price,
                };
                $addonTotal += $price;
                $addonDetails[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price_type' => $addon->price_type,
                    'unit_price' => (float) $addon->price,
                    'total' => round($price, 2),
                ];
            }
        }
        $addonTotal = round($addonTotal, 2);

        $subtotal = round($baseTotal + $addonTotal, 2);

        $discountAmount = 0.0;
        $promoDetails = null;
        if ($promoCode !== null) {
            $promoResult = $this->applyPromoCode($promoCode, $subtotal, $durationDays);
            if ($promoResult['valid']) {
                $discountAmount = $promoResult['discount'];
                $promoDetails = $promoResult;
            }
        }

        $afterDiscount = round(max(0.0, $subtotal - $discountAmount), 2);
        $taxRate = $this->getTaxRate();
        $taxAmount = round($afterDiscount * $taxRate, 2);
        $total = round($afterDiscount + $taxAmount, 2);

        return [
            'daily_rate' => $dailyRate,
            'surge_multiplier' => $surgeMultiplier,
            'effective_daily_rate' => $effectiveDailyRate,
            'duration_days' => $durationDays,
            'rental_type' => $rentalType,
            'rental_type_multiplier' => $rentalTypeMultiplier,
            'base_total' => $baseTotal,
            'addon_total' => $addonTotal,
            'addon_details' => $addonDetails,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'after_discount' => $afterDiscount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'deposit' => (float) $vehicle->deposit_amount,
            'total' => $total,
            'breakdown' => [
                'base_daily_rate' => $dailyRate,
                'surge_applied' => !empty($surgeBreakdown),
                'surge_details' => $surgeBreakdown,
                'driver_fee_per_day' => $rentalType === 'with_driver' ? $driverFeePerDay : 0,
                'addons' => $addonDetails,
                'promo_applied' => $discountAmount > 0,
                'promo_details' => $promoDetails,
            ],
        ];
    }

    /**
     * Get the surge pricing rule that applies for a vehicle on a given date.
     */
    public function getSurgePricing(Vehicle $vehicle, string $date): ?SurgePricingRule
    {
        $rules = SurgePricingRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($vehicle) {
                $query->whereNull('vehicle_category_id')
                    ->orWhere('vehicle_category_id', $vehicle->category_id);
            })
            ->where(function ($query) use ($vehicle) {
                $query->whereNull('location_id')
                    ->orWhere('location_id', $vehicle->location_id);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderByDesc('priority')
            ->get();

        foreach ($rules as $rule) {
            $carbon = Carbon::parse($date);
            if ($rule->isActiveForDateTime($carbon)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Validate and apply a promo code, returning discount info.
     */
    public function applyPromoCode(string $promoCode, float $subtotal, int $durationDays): array
    {
        $promo = PromoVoucher::where('code', $promoCode)->first();

        if (!$promo) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Kode promo tidak ditemukan.'];
        }

        if (!$promo->is_active) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Kode promo tidak aktif.'];
        }

        if ($promo->start_date && $promo->start_date->isFuture()) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Kode promo belum berlaku.'];
        }

        if ($promo->end_date && $promo->end_date->isPast()) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Kode promo sudah kedaluwarsa.'];
        }

        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            return ['valid' => false, 'discount' => 0.0, 'message' => 'Kode promo sudah mencapai batas penggunaan.'];
        }

        if ($promo->min_rental_days !== null && $durationDays < $promo->min_rental_days) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => 'Minimal sewa ' . $promo->min_rental_days . ' hari untuk kode promo ini.',
            ];
        }

        if ($promo->discount_type === 'percentage') {
            $discount = round($subtotal * ((float) $promo->discount_value / 100), 2);
            if ($promo->max_discount !== null) {
                $discount = min($discount, (float) $promo->max_discount);
            }
        } else {
            $discount = min((float) $promo->discount_value, $subtotal);
        }

        $discount = round($discount, 2);

        return [
            'valid' => true,
            'discount' => $discount,
            'message' => 'Berhasil menerapkan promo "' . $promo->name . '".',
        ];
    }

    /**
     * Calculate late return fee, choosing whichever is cheaper for the customer.
     */
    public function calculateLateFee(Vehicle $vehicle, int $lateMinutes): float
    {
        if ($lateMinutes <= 0) {
            return 0.0;
        }

        $hourlyRate = (float) $vehicle->late_fee_per_hour;
        $dailyRate = (float) $vehicle->late_fee_per_day;

        $hoursFee = 0.0;
        if ($hourlyRate > 0) {
            $hoursFee = round($hourlyRate * ceil($lateMinutes / 60), 2);
        }

        $daysFee = 0.0;
        if ($dailyRate > 0) {
            $daysFee = round($dailyRate * ceil($lateMinutes / 1440), 2);
        }

        if ($hoursFee === 0.0 && $daysFee === 0.0) {
            return 0.0;
        }

        if ($hoursFee === 0.0) {
            return $daysFee;
        }

        if ($daysFee === 0.0) {
            return $hoursFee;
        }

        return min($hoursFee, $daysFee);
    }

    /**
     * Calculate extra mileage fee.
     */
    public function calculateExtraKmFee(Vehicle $vehicle, int $extraKm): float
    {
        if ($extraKm <= 0) {
            return 0.0;
        }

        $ratePerKm = (float) SystemSetting::get('extra_km_rate', 5000);

        return round($extraKm * $ratePerKm, 2);
    }

    /**
     * Calculate driver fee for the given number of days.
     */
    public function calculateDriverFee(int $days, ?string $driverType = 'standard'): float
    {
        if ($days <= 0) {
            return 0.0;
        }

        $dailyRate = match ($driverType) {
            'standard' => (float) SystemSetting::get('driver_fee_standard', 200000),
            'senior' => (float) SystemSetting::get('driver_fee_senior', 300000),
            'vip' => (float) SystemSetting::get('driver_fee_vip', 500000),
            default => (float) SystemSetting::get('driver_fee_standard', 200000),
        };

        return round($dailyRate * $days, 2);
    }

    /**
     * Get the tax rate from system settings.
     */
    public function getTaxRate(): float
    {
        $rate = (float) SystemSetting::get('tax_rate', 11);

        return $rate > 1 ? $rate / 100 : $rate;
    }

    /**
     * Get the rental type multiplier.
     */
    public function getRentalTypeMultiplier(string $rentalType): float
    {
        return match ($rentalType) {
            'self_drive' => 1.0,
            'with_driver' => 1.0,
            'airport_transfer' => 1.5,
            'corporate' => 0.85,
            default => 1.0,
        };
    }
}
