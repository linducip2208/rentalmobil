<?php

namespace App\Services;

use App\Models\CorporateAccount;
use App\Models\RentalOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class CorporateBillingService
{
    /** Cek apakah order baru masih dalam limit kredit akun korporat. */
    public function checkCreditLimit(CorporateAccount $account, float $newOrderAmount): array
    {
        $outstanding = $account->outstandingBalance();
        $total = $outstanding + $newOrderAmount;

        return [
            'allowed' => $account->credit_limit <= 0 || $total <= $account->credit_limit,
            'outstanding' => $outstanding,
            'credit_limit' => (float) $account->credit_limit,
            'available' => $account->availableCredit(),
        ];
    }

    /** Baris statement gabungan seluruh order customer milik akun pada periode. */
    public function statementRows(CorporateAccount $account, $from, $to): Collection
    {
        $customerIds = $account->customers()->pluck('id');

        if ($customerIds->isEmpty()) {
            return collect();
        }

        return RentalOrder::with(['customer', 'vehicle', 'invoices'])
            ->whereIn('customer_id', $customerIds)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(function ($order) {
                $invoiced = (float) $order->invoices->sum('total_amount');
                $paid = (float) $order->invoices->sum('amount_paid');

                return [
                    'order' => $order,
                    'invoiced' => $invoiced,
                    'paid' => $paid,
                    'balance' => max(0, $invoiced - $paid),
                ];
            });
    }

    public function generateStatementPdf(CorporateAccount $account, $from, $to)
    {
        $rows = $this->statementRows($account, $from, $to);

        return Pdf::loadView('pdf.corporate-statement', [
            'account' => $account,
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'totalInvoiced' => $rows->sum('invoiced'),
            'totalPaid' => $rows->sum('paid'),
            'totalBalance' => $rows->sum('balance'),
            'outstanding' => $account->outstandingBalance(),
            'creditLimit' => (float) $account->credit_limit,
        ]);
    }
}
