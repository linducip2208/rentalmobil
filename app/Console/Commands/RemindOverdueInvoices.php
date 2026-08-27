<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class RemindOverdueInvoices extends Command
{
    protected $signature = 'finance:remind-overdue';

    protected $description = 'Surat penagihan bertingkat untuk invoice lewat jatuh tempo (dunning) — dedup per invoice 3 hari sekali';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['paid', 'cancelled', 'voided', 'draft'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->get();

        $sent = 0;

        foreach ($invoices as $invoice) {
            if (!$invoice->customer?->phone && !$invoice->customer?->email) {
                continue;
            }

            // Dedup: maksimal satu pengingat dunning per invoice dalam 3 hari.
            $recent = \App\Models\NotificationQueue::where('event_type', 'payment_dunning')
                ->where('payload->invoice_id', $invoice->id)
                ->where('created_at', '>=', now()->subDays(3))
                ->exists();

            if ($recent) {
                continue;
            }

            $daysLate = (int) now()->diffInDays($invoice->due_date);
            $urgency = match (true) {
                $daysLate > 30 => 'SEGERA: piutang lebih dari 30 hari. Tim koleksi akan menghubungi Anda.',
                $daysLate > 7 => 'Mohon segera lunasi untuk menghindari penahanan layanan berikutnya.',
                default => 'Mohon lakukan pembayaran.',
            };

            if ($dispatcher->dispatch('payment_dunning', $invoice->customer, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'balance_due' => number_format((float) ($invoice->balance_due ?: ($invoice->total_amount - $invoice->amount_paid)), 0, ',', '.'),
                'due_date' => $invoice->due_date->format('d M Y'),
                'days_late' => $daysLate,
                'urgency_message' => $urgency,
            ])) {
                $sent++;
            }
        }

        $this->info("Dunning dikirim untuk {$sent} invoice dari total {$invoices->count()} yang lewat tempo.");

        return self::SUCCESS;
    }
}
