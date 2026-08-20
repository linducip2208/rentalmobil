<?php

namespace App\Console\Commands;

use App\Models\ReturnRecord;
use App\Services\InvoiceGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceFromReturn extends Command
{
    protected $signature = 'invoices:generate-from-returns';

    protected $description = 'Auto-generate invoices for completed returns without invoices';

    public function handle(): int
    {
        $this->info('Checking for completed returns without invoices...');

        $returns = ReturnRecord::where('status', 'approved')
            ->whereDoesntHave('rentalOrder.invoices', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->with(['rentalOrder.customer', 'rentalOrder.vehicle'])
            ->get();

        if ($returns->isEmpty()) {
            $this->info('No returns need invoice generation.');
            return Command::SUCCESS;
        }

        $this->info("Found {$returns->count()} return(s) without invoices.");

        $invoiceService = app(InvoiceGenerationService::class);
        $generated = 0;
        $failed = 0;

        foreach ($returns as $return) {
            try {
                $order = $return->rentalOrder;

                if (!$order) {
                    $this->warn("  SKIP Return #{$return->id}: No linked order.");
                    $failed++;
                    continue;
                }

                $order->update([
                    'actual_return_date' => $return->return_date,
                ]);

                if ((float) $return->late_fee > 0) {
                    $order->update([
                        'late_fee' => $return->late_fee,
                    ]);
                }

                if ((float) $return->extra_charge > 0) {
                    $order->update([
                        'damage_fee' => $return->extra_charge,
                    ]);
                }

                $invoice = $invoiceService->generateFromOrder($order);

                $this->info("  ✓ Invoice {$invoice->invoice_number} generated for order {$order->order_number} (Rp {$invoice->total_amount})");
                $generated++;

                Log::info("Invoice generated from return", [
                    'return_id' => $return->id,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            } catch (\Exception $e) {
                $this->error("  ✗ Failed for Return #{$return->id}: {$e->getMessage()}");
                $failed++;

                Log::error("Invoice generation failed for return #{$return->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Invoice generation complete:");
        $this->info("  - Generated: {$generated}");
        $this->info("  - Failed: {$failed}");

        return Command::SUCCESS;
    }
}
