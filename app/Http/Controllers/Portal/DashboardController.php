<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\ReportPdfService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user('customer');
        $orders = RentalOrder::with('vehicle')->where('customer_id', $customer->id)->latest()->limit(5)->get();
        $invoices = Invoice::where('customer_id', $customer->id)->latest()->limit(5)->get();

        return view('portal.dashboard', compact('customer', 'orders', 'invoices'));
    }

    public function orders(Request $request)
    {
        $orders = RentalOrder::with(['vehicle', 'payments', 'invoices'])
            ->where('customer_id', $request->user('customer')->id)->latest()->paginate(15);
        return view('portal.orders', compact('orders'));
    }

    public function invoices(Request $request)
    {
        $invoices = Invoice::where('customer_id', $request->user('customer')->id)->latest()->paginate(15);
        return view('portal.invoices', compact('invoices'));
    }

    public function downloadInvoice(Request $request, Invoice $invoice, ReportPdfService $pdfs)
    {
        abort_unless($invoice->customer_id === $request->user('customer')->id, 404);
        return $pdfs->generateInvoicePdf($invoice)->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    public function uploadPaymentProof(Request $request, Invoice $invoice)
    {
        $customer = $request->user('customer');
        abort_unless($invoice->customer_id === $customer->id, 404);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$invoice->balance_due],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        $path = $request->file('proof')->store("payment-proofs/{$customer->id}", 'public');
        Payment::create([
            'invoice_id' => $invoice->id, 'rental_order_id' => $invoice->rental_order_id, 'customer_id' => $customer->id,
            'amount' => $data['amount'], 'payment_date' => today(), 'payment_time' => now()->format('H:i:s'),
            'reference_number' => $data['reference_number'] ?? null, 'proof_url' => $path, 'status' => 'pending',
            'notes' => 'Diupload mandiri melalui portal pelanggan.',
        ]);
        return back()->with('status', 'Bukti pembayaran terkirim dan menunggu verifikasi admin.');
    }
}
