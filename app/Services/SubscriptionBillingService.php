<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionBillingService
{
    /**
     * Terbitkan invoice untuk periode berjalan dan majukan current_period_end.
     * Tipe invoice 'additional' dipakai karena enum invoice tidak punya tipe subscription.
     */
    public function generateInvoice(Subscription $subscription): Invoice
    {
        return DB::transaction(function () use ($subscription) {
            $periodStart = $subscription->current_period_end ?? now();
            $periodEnd = $subscription->nextPeriodEnd();

            $invoice = Invoice::create([
                'customer_id' => $subscription->customer_id,
                'type' => 'additional',
                'subtotal' => $subscription->price_per_cycle,
                'total_amount' => $subscription->price_per_cycle,
                'balance_due' => $subscription->price_per_cycle,
                'due_date' => $periodStart->copy()->addDays(7),
                'status' => 'issued',
                'notes' => sprintf(
                    'Langganan %s — %s (%s → %s)',
                    $subscription->plan_name,
                    $subscription->vehicle?->name ?? '-',
                    $periodStart->format('d M Y'),
                    $periodEnd->copy()->subDay()->format('d M Y'),
                ),
            ]);

            $subscription->update(['current_period_end' => $periodEnd]);

            if ($subscription->start_date === null || $subscription->wasChanged()) {
                // start_date tidak diubah di sini.
            }

            return $invoice;
        });
    }

    /** Proses semua langganan aktif yang jatuh tempo. Return jumlah invoice diterbitkan. */
    public function runBilling(): int
    {
        $billed = 0;

        Subscription::with('vehicle')
            ->active()
            ->where(fn ($q) => $q->whereNull('current_period_end')->orWhere('current_period_end', '<=', now()))
            ->where('auto_renew', true)
            ->chunkById(100, function ($subscriptions) use (&$billed) {
                foreach ($subscriptions as $subscription) {
                    try {
                        $this->generateInvoice($subscription);
                        $billed++;
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            });

        return $billed;
    }

    public function cancel(Subscription $subscription, ?string $reason = null): void
    {
        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
            'cancelled_at' => today(),
            'notes' => trim(($subscription->notes ? $subscription->notes."\n" : '').'Dibatalkan: '.($reason ?? 'tanpa alasan')),
        ]);
    }
}
