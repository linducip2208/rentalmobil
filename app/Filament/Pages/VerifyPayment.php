<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use Filament\Pages\Page;

class VerifyPayment extends Page
{
    protected string $view = 'filament.pages.verify-payment';

    protected static ?string $title = 'Verifikasi Pembayaran';

    public ?int $paymentId = null;
    public ?array $payment = null;
    public bool $processed = false;
    public string $action = 'approve';
    public string $notes = '';

    public function mount(?int $payment = null): void
    {
        if (! $payment) {
            abort(404);
        }

        $this->paymentId = $payment;

        $paymentModel = Payment::with(['customer', 'paymentMethod', 'invoice', 'rentalOrder'])
            ->find($payment);

        if (! $paymentModel) {
            abort(404);
        }

        $this->payment = [
            'id' => $paymentModel->id,
            'payment_number' => $paymentModel->payment_number,
            'customer_name' => $paymentModel->customer?->name ?? '-',
            'amount' => $paymentModel->amount,
            'payment_date' => $paymentModel->payment_date?->format('d M Y'),
            'payment_method' => $paymentModel->paymentMethod?->name ?? '-',
            'reference_number' => $paymentModel->reference_number ?? '-',
            'proof_url' => $paymentModel->proof_url,
            'invoice_number' => $paymentModel->invoice?->invoice_number ?? '-',
            'order_number' => $paymentModel->rentalOrder?->order_number ?? '-',
            'status' => $paymentModel->status,
            'notes' => $paymentModel->notes,
        ];
    }

    public function processPayment(): void
    {
        $payment = Payment::find($this->paymentId);

        if (! $payment || $payment->status !== 'pending') {
            session()->flash('error', 'Pembayaran tidak valid atau sudah diproses.');

            return;
        }

        if ($this->action === 'approve') {
            $payment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $this->notes ?: $payment->notes,
            ]);

            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
                $invoice->amount_paid = (float) $invoice->amount_paid + (float) $payment->amount;
                $invoice->balance_due = (float) $invoice->total_amount - (float) $invoice->amount_paid;

                if ($invoice->balance_due <= 0) {
                    $invoice->status = 'paid';
                    $invoice->paid_at = now();
                } else {
                    $invoice->status = 'partially_paid';
                }

                $invoice->save();
            }

            if ($payment->rental_order_id) {
                $order = $payment->rentalOrder;
                $order->amount_paid = (float) $order->amount_paid + (float) $payment->amount;
                $order->balance_due = (float) $order->final_amount - (float) $order->amount_paid;

                if ($order->balance_due <= 0) {
                    $order->payment_status = 'paid';
                } else {
                    $order->payment_status = 'partial';
                }

                $order->save();
            }

            session()->flash('success', "Pembayaran #{$payment->payment_number} berhasil diverifikasi.");
        } else {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $this->notes ?: 'Ditolak',
            ]);

            session()->flash('success', "Pembayaran #{$payment->payment_number} telah ditolak.");
        }

        $this->processed = true;
    }
}
