<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\RentalOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService
{
    public function generateFromOrder(RentalOrder $order): Invoice
    {
        $existingInvoice = Invoice::where('rental_order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        $lineItems = $this->buildLineItems($order);
        $subtotal = $lineItems->sum('total_price');
        $taxRate = (float) ($this->getTaxRate());
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount - (float) $order->discount_amount, 2);

        $dueDate = $order->start_date->copy()->addDays(
            (int) SystemSetting::get('invoice_due_days', 7)
        );

        return DB::transaction(function () use ($order, $lineItems, $subtotal, $taxAmount, $totalAmount, $dueDate) {
            $invoice = Invoice::create([
                'rental_order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => (float) $order->discount_amount,
                'total_amount' => $totalAmount,
                'amount_paid' => 0.00,
                'due_date' => $dueDate,
                'status' => 'unpaid',
                'notes' => "Invoice for order {$order->order_number}",
            ]);

            foreach ($lineItems as $item) {
                RentalOrderItem::create([
                    'rental_order_id' => $order->id,
                    'addon_id' => $item['addon_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'type' => $item['type'],
                ]);
            }

            return $invoice;
        });
    }

    public function generateAdditionalCharge(RentalOrder $order, string $description, float $amount): Invoice
    {
        $taxRate = (float) $this->getTaxRate();
        $taxAmount = round($amount * $taxRate, 2);
        $totalAmount = round($amount + $taxAmount, 2);

        $existingInvoice = Invoice::where('rental_order_id', $order->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->first();

        if ($existingInvoice) {
            $existingInvoice->update([
                'subtotal' => round((float) $existingInvoice->subtotal + $amount, 2),
                'tax_amount' => round((float) $existingInvoice->tax_amount + $taxAmount, 2),
                'total_amount' => round((float) $existingInvoice->total_amount + $totalAmount, 2),
            ]);

            RentalOrderItem::create([
                'rental_order_id' => $order->id,
                'name' => $description,
                'description' => $description,
                'quantity' => 1,
                'unit_price' => number_format($amount, 2, '.', ''),
                'total_price' => number_format($amount, 2, '.', ''),
                'type' => 'additional_charge',
            ]);

            return $existingInvoice;
        }

        $dueDate = Carbon::now()->addDays((int) SystemSetting::get('invoice_due_days', 7));

        $invoice = Invoice::create([
            'rental_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'subtotal' => $amount,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0.00,
            'total_amount' => $totalAmount,
            'amount_paid' => 0.00,
            'due_date' => $dueDate,
            'status' => 'unpaid',
            'notes' => "Additional charge: {$description}",
        ]);

        RentalOrderItem::create([
            'rental_order_id' => $order->id,
            'name' => $description,
            'description' => $description,
            'quantity' => 1,
            'unit_price' => number_format($amount, 2, '.', ''),
            'total_price' => number_format($amount, 2, '.', ''),
            'type' => 'additional_charge',
        ]);

        return $invoice;
    }

    public function generateRefund(RentalOrder $order, float $amount): Invoice
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be greater than zero.');
        }

        $taxRate = (float) $this->getTaxRate();
        $taxAmount = round($amount * $taxRate, 2);
        $totalAmount = round($amount + $taxAmount, 2);

        $dueDate = Carbon::now();

        $invoice = Invoice::create([
            'rental_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'subtotal' => -$amount,
            'tax_amount' => -$taxAmount,
            'discount_amount' => 0.00,
            'total_amount' => -$totalAmount,
            'amount_paid' => 0.00,
            'due_date' => $dueDate,
            'status' => 'unpaid',
            'notes' => "Refund for order {$order->order_number}",
        ]);

        RentalOrderItem::create([
            'rental_order_id' => $order->id,
            'name' => 'Refund',
            'description' => "Refund - {$order->order_number}",
            'quantity' => 1,
            'unit_price' => number_format(-$amount, 2, '.', ''),
            'total_price' => number_format(-$amount, 2, '.', ''),
            'type' => 'refund',
        ]);

        return $invoice;
    }

    public function calculateBalanceDue(Invoice $invoice): float
    {
        return round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2);
    }

    protected function buildLineItems(RentalOrder $order): \Illuminate\Support\Collection
    {
        $items = collect();

        $days = max(1, (float) $order->duration_days);
        $items->push([
            'name' => "Rental {$order->vehicle->name}",
            'description' => "{$days} hari sewa ({$order->start_date->format('d M')} - {$order->end_date->format('d M Y')})",
            'quantity' => (int) $days,
            'unit_price' => (float) $order->daily_rate,
            'total_price' => round($days * (float) $order->daily_rate, 2),
            'type' => 'rental',
            'addon_id' => null,
        ]);

        if ($order->driver) {
            $driverCost = (float) SystemSetting::get('driver_daily_cost', 150000);
            $items->push([
                'name' => 'Biaya Supir',
                'description' => "{$days} hari layanan supir",
                'quantity' => (int) $days,
                'unit_price' => $driverCost,
                'total_price' => round($days * $driverCost, 2),
                'type' => 'driver',
                'addon_id' => null,
            ]);
        }

        $addons = $order->items()->whereNotNull('addon_id')->get();
        foreach ($addons as $addonItem) {
            $items->push([
                'name' => $addonItem->name,
                'description' => $addonItem->description ?? $addonItem->name,
                'quantity' => (int) $addonItem->quantity,
                'unit_price' => (float) $addonItem->unit_price,
                'total_price' => (float) $addonItem->total_price,
                'type' => 'addon',
                'addon_id' => $addonItem->addon_id,
            ]);
        }

        if ((float) $order->late_fee > 0) {
            $items->push([
                'name' => 'Biaya Keterlambatan',
                'description' => 'Denda pengembalian melewati batas waktu',
                'quantity' => 1,
                'unit_price' => (float) $order->late_fee,
                'total_price' => (float) $order->late_fee,
                'type' => 'late_fee',
                'addon_id' => null,
            ]);
        }

        if ((float) $order->damage_fee > 0) {
            $items->push([
                'name' => 'Biaya Kerusakan',
                'description' => 'Denda kerusakan kendaraan',
                'quantity' => 1,
                'unit_price' => (float) $order->damage_fee,
                'total_price' => (float) $order->damage_fee,
                'type' => 'damage_fee',
                'addon_id' => null,
            ]);
        }

        return $items;
    }

    protected function getTaxRate(): float
    {
        return (float) SystemSetting::get('tax_rate', 0.11);
    }
}
