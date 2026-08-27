<?php

namespace App\Services;

use App\Models\EarlyBirdRule;
use App\Models\FlashSale;
use App\Models\Vehicle;

/**
 * Modifier diskon waktu pada quote: early-bird (booking jauh hari) & flash sale (time-boxed).
 */
class DiscountModifierService
{
    public function apply(Vehicle $vehicle, string $startDate, array $quote): array
    {
        $leadDays = max(0, now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($startDate)->startOfDay()));

        $earlyBird = $this->findEarlyBirdRule($vehicle, $leadDays);

        if ($earlyBird) {
            $discount = $earlyBird->discount_type === 'percentage'
                ? round($quote['subtotal'] * ((float) $earlyBird->discount_value / 100), 2)
                : min((float) $earlyBird->discount_value, $quote['subtotal']);

            if ($discount > 0) {
                $quote = $this->mergeDiscount($quote, $discount, 'Early Bird: ' . $earlyBird->name);
            }
        }

        $flashSale = FlashSale::live()
            ->get()
            ->first(fn (FlashSale $sale) => $sale->coversVehicle($vehicle));

        if ($flashSale) {
            // Flash sale dihitung dari base_total sebelum diskon lain agar tidak tumpang tindih berlebihan.
            $discount = $flashSale->discountFor((float) $quote['base_total']);

            if ($discount > 0) {
                $quote = $this->mergeDiscount($quote, $discount, 'Flash Sale: ' . $flashSale->name, flash_sale_id: $flashSale->id);
            }
        }

        return $quote;
    }

    public function findEarlyBirdRule(Vehicle $vehicle, int $leadDays): ?EarlyBirdRule
    {
        return EarlyBirdRule::forVehicle($vehicle)
            ->get()
            ->first(fn (EarlyBirdRule $rule) => $rule->matchesLeadDays($leadDays));
    }

    protected function mergeDiscount(array $quote, float $amount, string $label, ?int $flashSaleId = null): array
    {
        $newDiscount = round(min($amount, (float) $quote['after_discount']), 2);

        $quote['discount_amount'] = round((float) $quote['discount_amount'] + $newDiscount, 2);
        $quote['after_discount'] = round(max(0.0, (float) $quote['subtotal'] - $quote['discount_amount']), 2);
        $quote['tax_amount'] = round($quote['after_discount'] * $quote['tax_rate'], 2);
        $quote['total'] = round($quote['after_discount'] + $quote['tax_amount'], 2);

        $modifiers = $quote['breakdown']['time_modifiers'] ?? [];
        $modifiers[] = [
            'label' => $label,
            'discount' => $newDiscount,
        ];
        $quote['breakdown']['time_modifiers'] = $modifiers;

        if ($flashSaleId) {
            $quote['breakdown']['flash_sale_id'] = $flashSaleId;
        }

        return $quote;
    }
}
