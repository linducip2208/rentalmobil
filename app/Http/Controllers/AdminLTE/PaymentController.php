<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['invoice.customer', 'invoice.rentalOrder.vehicle'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('lte.payments.index', [
            'payments' => $payments,
            'statuses' => ['pending' => 'Menunggu Verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'],
        ]);
    }

    public function verify(Payment $payment, PaymentService $service)
    {
        try {
            $service->verifyPayment($payment, auth()->id());

            return back()->with('status', 'Pembayaran terverifikasi. Invoice & order diperbarui otomatis.');
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }

    public function reject(Request $request, Payment $payment, PaymentService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $service->rejectPayment($payment, auth()->id(), $data['reason']);

            return back()->with('status', 'Pembayaran ditolak.');
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }
}
