<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->customer;

        $invoices = $customer->invoices()
            ->with('rentalOrder.vehicle')
            ->latest()
            ->paginate(10);

        return view('portal.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $customer = auth('customer')->user()->customer;

        abort_if($invoice->customer_id !== $customer->id, 403);

        $invoice->load(['rentalOrder.vehicle', 'payments.paymentMethod']);

        return view('portal.invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $customer = auth('customer')->user()->customer;

        abort_if($invoice->customer_id !== $customer->id, 403);

        $invoice->load(['rentalOrder.vehicle', 'customer', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4')
            ->setOptions(['isPhpEnabled' => true, 'defaultFont' => 'sans-serif']);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
