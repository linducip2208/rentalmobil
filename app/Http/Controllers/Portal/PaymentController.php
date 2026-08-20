<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $customer = auth('customer')->user()->customer;

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'proof' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        abort_if($invoice->customer_id !== $customer->id, 403);

        $proofPath = $request->file('proof')->store('payments/proof', 'public');

        Payment::create([
            'invoice_id' => $invoice->id,
            'rental_order_id' => $invoice->rental_order_id,
            'customer_id' => $customer->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_url' => $proofPath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('portal.invoices.show', $invoice)
            ->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi.');
    }
}
