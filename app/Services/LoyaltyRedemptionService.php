<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyLedger;
use App\Models\RentalOrder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

/**
 * Poin loyalitas: earn dari transaksi (LoyaltyService::calculatePoints),
 * redeem jadi potongan order, plus audit trail via loyalty_ledgers.
 */
class LoyaltyRedemptionService
{
    public function balance(Customer $customer): int
    {
        return (int) ($customer->loyalty_points ?? 0);
    }

    public function earn(Customer $customer, int $points, string $description, ?object $reference = null): LoyaltyLedger
    {
        return DB::transaction(function () use ($customer, $points, $description, $reference) {
            $balance = $this->balance($customer) + $points;

            $customer->increment('loyalty_points', $points);

            return LoyaltyLedger::create([
                'customer_id' => $customer->id,
                'type' => 'earn',
                'points' => $points,
                'balance_after' => $balance,
                'description' => $description,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->id,
            ]);
        });
    }

    public function earnFromOrder(Customer $customer, RentalOrder $order): ?LoyaltyLedger
    {
        $loyalty = app(LoyaltyService::class);
        $points = $loyalty->calculatePoints((float) $order->total_amount, $customer->loyalty_tier ?? 'bronze');

        if ($points <= 0) {
            return null;
        }

        return $this->earn($customer, $points, "Poin dari pesanan {$order->order_number}", $order);
    }

    public function redeem(Customer $customer, int $points, string $description = '', ?object $reference = null): array
    {
        $maxRedeemable = $this->maxRedeemable();

        abort_if($points <= 0, 422, 'Jumlah poin tidak valid.');
        abort_if($points > $maxRedeemable, 422, "Maksimal {$maxRedeemable} poin per penukaran.");
        abort_unless($this->balance($customer) >= $points, 422, 'Poin tidak cukup.');

        return DB::transaction(function () use ($customer, $points, $description, $reference) {
            $customer->decrement('loyalty_points', $points);

            LoyaltyLedger::create([
                'customer_id' => $customer->id,
                'type' => 'redeem',
                'points' => -$points,
                'balance_after' => $this->balance($customer),
                'description' => $description ?: 'Penukaran poin',
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->id,
            ]);

            return [
                'redeemed_points' => $points,
                'credit_amount' => $this->pointValue($points),
                'remaining_balance' => $this->balance($customer),
            ];
        });
    }

    /**
     * Nilai rupiah per poin — diatur pemilik via SystemSetting "loyalty_point_value" (IDR).
     */
    public function pointValue(int $points): float
    {
        return round($points * (float) SystemSetting::get('loyalty_point_value', 100), 2);
    }

    public function maxRedeemable(): int
    {
        return (int) SystemSetting::get('loyalty_max_redeem_per_order', 500);
    }

    public function adjust(Customer $customer, int $deltaPoints, string $reason): LoyaltyLedger
    {
        $type = $deltaPoints >= 0 ? 'adjust' : 'adjust';

        if ($deltaPoints >= 0) {
            $customer->increment('loyalty_points', $deltaPoints);
        } else {
            $customer->decrement('loyalty_points', abs($deltaPoints));
        }

        return LoyaltyLedger::create([
            'customer_id' => $customer->id,
            'type' => $type,
            'points' => $deltaPoints,
            'balance_after' => $this->balance($customer),
            'description' => $reason,
        ]);
    }
}
