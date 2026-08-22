<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DepositRefundService
{
    /**
     * Refund deposit dengan potongan terstruktur.
     * Potongan umum rental: kurang BBM (liter x harga), biaya cuci, denda telat, kerusakan minor.
     * Jika ada potongan, otomatis diterbitkan invoice tipe 'penalty'.
     */
    public function refund(Deposit $deposit, array $deductions, int $userId, ?string $method = null): Deposit
    {
        abort_unless(in_array($deposit->deposit_status, ['received', 'held'], true), 422, 'Deposit tidak dalam status yang bisa direfund.');

        return DB::transaction(function () use ($deposit, $deductions, $userId) {
            $detail = [];
            $totalDeduction = 0.0;

            foreach ($deductions as $type => $amount) {
                $amount = round((float) $amount, 2);

                if ($amount <= 0) {
                    continue;
                }

                $labels = [
                    'fuel' => 'Kurang BBM',
                    'cleaning' => 'Biaya cuci',
                    'late_fee' => 'Denda keterlambatan',
                    'damage' => 'Kerusakan minor',
                    'other' => 'Potongan lain',
                ];

                $label = $labels[$type] ?? ucfirst($type);
                $detail[] = "{$label}: Rp ".number_format($amount, 0, ',', '.');
                $totalDeduction += $amount;
            }

            $net = max(0, (float) $deposit->amount - $totalDeduction);

            if ($totalDeduction > 0 && $deposit->rental_order_id) {
                Invoice::create([
                    'rental_order_id' => $deposit->rental_order_id,
                    'customer_id' => $deposit->customer_id,
                    'type' => 'penalty',
                    'subtotal' => $totalDeduction,
                    'total_amount' => $totalDeduction,
                    'balance_due' => $totalDeduction,
                    'due_date' => now()->addDays(7),
                    'status' => 'issued',
                    'notes' => "Potongan deposit:\n".implode("\n", $detail),
                ]);
            }

            $deposit->update([
                'deposit_status' => 'refunded',
                'refund_amount' => $net,
                'refund_method' => 'cash_transfer_manual',
                'refunded_at' => now(),
                'approved_by' => $userId,
                'notes' => trim(($deposit->notes ? $deposit->notes."\n" : '')."Refund Rp ".number_format($net, 0, ',', '.')."\n".implode("\n", $detail)),
            ]);

            return $deposit->fresh();
        });
    }
}
