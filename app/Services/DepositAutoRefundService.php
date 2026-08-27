<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

/**
 * Auto-refund deposit: setelah return lolos inspeksi tanpa kerusakan,
 * deposit dijadwalkan refund otomatis sesuai SLA hari (SystemSetting "deposit_refund_sla_days").
 */
class DepositAutoRefundService
{
    public function scheduleEligibleRefunds(): array
    {
        $slaDays = (int) SystemSetting::get('deposit_refund_sla_days', 3);
        $scheduled = 0;

        $deposits = Deposit::query()
            ->whereIn('deposit_status', ['held', 'received'])
            ->whereNull('refunded_at')
            ->whereNull('auto_refund_scheduled_at')
            ->whereHas('rentalOrder', function ($q) {
                $q->where('status', 'completed');
            })
            ->whereDoesntHave('rentalOrder.damageReports')
            ->with('rentalOrder.returnRecords')
            ->get();

        foreach ($deposits as $deposit) {
            $return = $deposit->rentalOrder->returnRecords->sortByDesc('returned_at')->first();

            if (! $return || ! in_array($return->condition_status, ['good', 'excellent', 'ok'])) {
                continue;
            }

            $eligibleAt = ($return?->returned_at ?? now())->copy()->addDays($slaDays);

            if ($eligibleAt->isPast()) {
                $eligibleAt = now();
            }

            $deposit->update([
                'auto_refund_scheduled_at' => $eligibleAt,
                'refund_channel' => 'original_payment_method',
            ]);

            $scheduled++;
        }

        return ['scheduled' => $scheduled, 'sla_days' => $slaDays];
    }

    /**
     * Proses refund yang sudah jatuh tempo.
     * Mode "auto_mark" (default): deposit ditandai refunded — tim finance
     * mengeksekusi transfer sesuai channel. Mode "gateway": dibuat transaksi
     * refund via provider pembayaran dinamis; gagal → masuk antrean manual.
     */
    public function processDueRefunds(): array
    {
        $results = ['processed' => 0, 'pending_manual' => 0];

        $dueDeposits = Deposit::whereNotNull('auto_refund_scheduled_at')
            ->whereNull('refunded_at')
            ->where('auto_refund_scheduled_at', '<=', now())
            ->with(['customer', 'paymentMethod'])
            ->get();

        foreach ($dueDeposits as $deposit) {
            DB::transaction(function () use ($deposit, &$results) {
                $mode = SystemSetting::get('deposit_auto_refund_mode', 'auto_mark');

                if ($mode === 'gateway') {
                    try {
                        app(PaymentGatewayService::class)->createRefundTransaction($deposit);
                    } catch (\Throwable) {
                        $results['pending_manual']++;

                        return;
                    }
                }

                $deposit->update([
                    'refund_amount' => $deposit->amount,
                    'refunded_at' => now(),
                    'deposit_status' => 'refunded',
                    'refund_method' => $deposit->refund_channel ?? 'transfer',
                ]);
                $results['processed']++;
            });
        }

        return $results;
    }
}
