<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PromoVoucher;
use App\Models\RentalOrder;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function applyToBooking(Booking $booking, int $voucherId, int $customerId): VoucherUsage
    {
        $voucher = PromoVoucher::findOrFail($voucherId);

        if (! $voucher->isValid()) {
            throw new \RuntimeException("Voucher '{$voucher->code}' is not valid or has expired.");
        }

        if ($voucher->minimum_amount && (float) $booking->subtotal < (float) $voucher->minimum_amount) {
            throw new \RuntimeException(
                'Minimum order amount is '.number_format($voucher->minimum_amount, 0, ',', '.').'.'
            );
        }

        if ($voucher->min_rental_days && (int) $booking->duration_days < (int) $voucher->min_rental_days) {
            throw new \RuntimeException('Voucher membutuhkan minimal sewa '.$voucher->min_rental_days.' hari.');
        }

        // Concurrency-safe quota: re-check inside a locked transaction before
        // incrementing used_count so parallel checkouts cannot overshoot.
        return DB::transaction(function () use ($booking, $voucher, $customerId) {
            $voucher = PromoVoucher::whereKey($voucher->id)->lockForUpdate()->first();

            if (! $voucher || ! $voucher->isValid()) {
                throw new \RuntimeException("Voucher '{$voucher->code}' is not valid or has expired.");
            }

            $existingUsage = VoucherUsage::where('voucher_id', $voucher->id)
                ->where('customer_id', $customerId)
                ->where('booking_id', $booking->id)
                ->exists();

            if ($existingUsage) {
                throw new \RuntimeException('Voucher already applied to this booking.');
            }

            $discountAmount = $voucher->calculateDiscount((float) $booking->subtotal);

            $usage = VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'booking_id' => $booking->id,
                'customer_id' => $customerId,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);

            $newSubtotal = max(0, (float) $booking->subtotal - $discountAmount);
            $taxRate = (float) SystemSetting::get('tax_rate', 0.11);
            $taxAmount = round($newSubtotal * $taxRate, 2);

            $booking->update([
                'discount_amount' => round((float) $booking->discount_amount + $discountAmount, 2),
                'tax_amount' => $taxAmount,
                'total_amount' => round($newSubtotal + $taxAmount, 2),
            ]);

            $voucher->increment('used_count');

            return $usage;
        });
    }

    public function applyToOrder(RentalOrder $order, int $voucherId, int $customerId): VoucherUsage
    {
        $voucher = PromoVoucher::findOrFail($voucherId);

        if (! $voucher->isValid()) {
            throw new \RuntimeException("Voucher '{$voucher->code}' is not valid or has expired.");
        }

        if ($voucher->minimum_amount && (float) $order->subtotal < (float) $voucher->minimum_amount) {
            throw new \RuntimeException(
                'Minimum order amount is '.number_format($voucher->minimum_amount, 0, ',', '.').'.'
            );
        }

        $existingUsage = VoucherUsage::where('voucher_id', $voucherId)
            ->where('customer_id', $customerId)
            ->where('rental_order_id', $order->id)
            ->exists();

        if ($existingUsage) {
            throw new \RuntimeException('Voucher already applied to this order.');
        }

        $discountAmount = $voucher->calculateDiscount((float) $order->subtotal);

        return DB::transaction(function () use ($order, $voucher, $customerId, $discountAmount) {
            $usage = VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'rental_order_id' => $order->id,
                'customer_id' => $customerId,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);

            $newSubtotal = max(0, (float) $order->subtotal - $discountAmount);
            $taxRate = (float) SystemSetting::get('tax_rate', 0.11);
            $taxAmount = round($newSubtotal * $taxRate, 2);

            $order->update([
                'discount_total' => round((float) $order->discount_total + $discountAmount, 2),
                'tax_total' => $taxAmount,
                'final_amount' => round($newSubtotal + $taxAmount, 2),
                'balance_due' => round(max(0, $newSubtotal + $taxAmount - (float) $order->amount_paid), 2),
            ]);

            $voucher->increment('used_count');

            return $usage;
        });
    }

    public function validateVoucher(string $code, float $amount): array
    {
        $voucher = PromoVoucher::where('code', $code)->first();

        if (! $voucher) {
            return ['valid' => false, 'message' => 'Voucher not found.'];
        }

        if (! $voucher->isValid()) {
            return ['valid' => false, 'message' => 'Voucher has expired or is inactive.'];
        }

        if ($voucher->minimum_amount && $amount < (float) $voucher->minimum_amount) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount is '.number_format($voucher->minimum_amount, 0, ',', '.').'.',
            ];
        }

        $discount = $voucher->calculateDiscount($amount);

        return [
            'valid' => true,
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'type' => $voucher->type,
            'value' => (float) $voucher->value,
            'discount_amount' => $discount,
            'message' => 'Discount of '.number_format($discount, 0, ',', '.').' applied.',
        ];
    }
}
