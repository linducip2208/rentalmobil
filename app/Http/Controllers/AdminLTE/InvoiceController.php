<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with('customer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('invoice_number', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('lte.invoices.index', [
            'invoices' => $invoices,
            'statuses' => ['issued' => 'Terbit', 'partially_paid' => 'Sebagian Dibayar', 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', 'cancelled' => 'Dibatalkan'],
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('lte.invoices.show', [
            'invoice' => $invoice->load(['customer', 'payments', 'rentalOrder.vehicle']),
        ]);
    }
}
