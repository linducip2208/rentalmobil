<?php

namespace App\Services;

use App\Models\RentalExtension;
use Illuminate\Support\Facades\DB;

class RentalExtensionService
{
    public function approve(RentalExtension $e, int $userId): RentalExtension
    {
        return DB::transaction(function () use ($e, $userId) {
            abort_unless($e->status === 'pending', 422);
            $order = $e->rentalOrder;
            $order->update(['end_date' => $e->requested_end_date, 'status' => 'active', 'final_amount' => (float) $order->final_amount + (float) $e->additional_amount, 'balance_due' => (float) $order->balance_due + (float) $e->additional_amount]);
            app(InvoiceGenerationService::class)->generateAdditionalCharge($order, 'Perpanjangan rental', (float) $e->additional_amount);
            $e->update(['status' => 'approved', 'reviewed_by' => $userId, 'reviewed_at' => now()]);
            app(NotificationDispatcher::class)->dispatch('rental_extension_approved', $e->customer, ['order_number' => $order->order_number, 'new_end_date' => $e->requested_end_date->translatedFormat('d M Y'), 'additional_amount' => number_format((float) $e->additional_amount, 0, ',', '.')]);

            return $e->fresh();
        });
    }

    public function reject(RentalExtension $e, int $userId, string $reason): RentalExtension
    {
        $e->update(['status' => 'rejected', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'reason' => $reason]);
        $e->rentalOrder->update(['status' => 'active']);

        return $e->fresh();
    }
}
