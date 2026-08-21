<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RentalOrder;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;

class PaymentService
{
    public function __construct(
        protected AccountingService $accounting,
    ) {}

    public function recordPayment(array $data): Payment
    {
        $invoice = Invoice::findOrFail($data['invoice_id']);

        if ($invoice->status === 'paid') {
            throw new \RuntimeException('Invoice is already fully paid.');
        }

        $amount = (float) $data['amount'];
        $balanceDue = round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2);

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if ($amount > $balanceDue) {
            throw new \InvalidArgumentException(
                "Payment amount ({$amount}) exceeds balance due ({$balanceDue})."
            );
        }

        return DB::transaction(function () use ($data, $invoice, $amount) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'rental_order_id' => $invoice->rental_order_id,
                'customer_id' => $invoice->customer_id,
                'payment_method_id' => $data['payment_method_id'],
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'proof_url' => $data['proof_url'] ?? $data['proof_path'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            return $payment;
        });
    }

    public function recordAndVerifyGatewayPayment(PaymentTransaction $transaction): Payment
    {
        $invoice = $transaction->invoice;
        $methodId = data_get($transaction->provider->config, 'payment_method_id');
        if (!$methodId || !\App\Models\PaymentMethod::whereKey($methodId)->where('is_active', true)->exists()) {
            throw new \RuntimeException('Pilih metode pembayaran aktif pada konfigurasi provider.');
        }
        $payment = $this->recordPayment(['invoice_id'=>$invoice->id,'payment_method_id'=>$methodId,'amount'=>$transaction->amount,'payment_date'=>today(),'reference_number'=>$transaction->external_id,'notes'=>'Pembayaran gateway BYOK '.$transaction->public_id]);
        return $this->verifyPayment($payment, null);
    }

    public function verifyPayment(Payment $payment, ?int $userId, ?float $amount = null): Payment
    {
        if ($payment->status === 'verified') {
            throw new \RuntimeException('Payment is already verified.');
        }

        if ($payment->status === 'rejected') {
            throw new \RuntimeException('Cannot verify a rejected payment. Create a new payment record.');
        }

        $verifyAmount = $amount ?? (float) $payment->amount;

        return DB::transaction(function () use ($payment, $userId, $verifyAmount) {
            $payment->update([
                'status' => 'verified',
                'verified_by' => $userId,
                'verified_at' => now(),
            ]);

            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $newTotalPaid = (float) $invoice->payments()
                    ->where('status', 'verified')
                    ->sum('amount');

                $newStatus = match (true) {
                    $newTotalPaid >= (float) $invoice->total_amount => 'paid',
                    $newTotalPaid > 0 => 'partially_paid',
                    default => 'issued',
                };

                $invoice->update([
                    'amount_paid' => round($newTotalPaid, 2),
                    'balance_due' => round(max(0, (float) $invoice->total_amount - $newTotalPaid), 2),
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                ]);

                $order = RentalOrder::find($invoice->rental_order_id);
                if ($order) {
                    $orderPaid = $order->invoices()
                        ->whereIn('status', ['paid', 'partial'])
                        ->sum('amount_paid');

                    $order->update([
                        'amount_paid' => round($orderPaid, 2),
                        'balance_due' => round(max(0, (float) $order->final_amount - $orderPaid), 2),
                        'payment_status' => $newStatus === 'paid' ? 'paid' : 'partially_paid',
                    ]);
                }
            }

            $this->accounting->recordPayment($payment);

            return $payment->fresh();
        });
    }

    public function rejectPayment(Payment $payment, int $userId, ?string $reason = null): Payment
    {
        if ($payment->status === 'verified') {
            throw new \RuntimeException('Cannot reject an already verified payment.');
        }

        if ($payment->status === 'rejected') {
            throw new \RuntimeException('Payment is already rejected.');
        }

        return DB::transaction(function () use ($payment, $userId, $reason) {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => $userId,
                'verified_at' => now(),
                'notes' => $reason
                    ? ($payment->notes ? "{$payment->notes}\nRejection: {$reason}" : "Rejection: {$reason}")
                    : $payment->notes,
            ]);

            $order = RentalOrder::find($payment->rental_order_id);
            if ($order) {
                $orderPaid = $order->payments()
                    ->where('status', 'verified')
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                $order->update([
                    'amount_paid' => round($orderPaid, 2),
                ]);
            }

            return $payment->fresh();
        });
    }

    public function getPaymentSummary(int $customerId): array
    {
        $payments = Payment::where('customer_id', $customerId);

        $totalPaid = (clone $payments)
            ->where('status', 'verified')
            ->sum('amount');

        $pendingAmount = (clone $payments)
            ->where('status', 'pending')
            ->sum('amount');

        $rejectedAmount = (clone $payments)
            ->where('status', 'rejected')
            ->sum('amount');

        $overdueInvoices = Invoice::where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $overdueAmount = (float) $overdueInvoices->sum('total_amount')
            - (float) $overdueInvoices->sum('amount_paid');

        $totalSpent = $totalPaid - $rejectedAmount;

        return [
            'customer_id' => $customerId,
            'total_paid' => round((float) $totalPaid, 2),
            'total_spent' => round(max(0, $totalSpent), 2),
            'pending_amount' => round((float) $pendingAmount, 2),
            'overdue_amount' => round(max(0, $overdueAmount), 2),
            'rejected_amount' => round((float) $rejectedAmount, 2),
            'overdue_invoices_count' => $overdueInvoices->count(),
            'total_transactions' => Payment::where('customer_id', $customerId)->count(),
        ];
    }
}
