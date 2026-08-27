<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\ReportPdfService;
use App\Models\Provider;
use App\Models\RentalExtension;
use App\Services\PaymentGatewayService;
use App\Services\PricingEngine;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user('customer');
        $orders = RentalOrder::with('vehicle')->where('customer_id', $customer->id)->latest()->limit(5)->get();
        $invoices = Invoice::where('customer_id', $customer->id)->latest()->limit(5)->get();

        return view('portal.dashboard', compact('customer', 'orders', 'invoices'));
    }

    public function referrals(Request $request)
    {
        $customer = $request->user('customer');
        $service = app(\App\Services\ReferralService::class);

        $stats = $service->statsFor($customer);
        $referrals = \App\Models\Referral::where('referrer_customer_id', $customer->id)->latest()->paginate(15);
        $bookingUrl = config('app.url') . '/booking?ref=' . $stats['code'];

        return view('portal.referrals', compact('customer', 'stats', 'referrals', 'bookingUrl'));
    }

    public function loyaltyPoints(Request $request)
    {
        $customer = $request->user('customer');
        $loyalty = app(\App\Services\LoyaltyRedemptionService::class);

        $balance = $loyalty->balance($customer);
        $pointValue = $loyalty->pointValue($balance);
        $ledgers = \App\Models\LoyaltyLedger::where('customer_id', $customer->id)->latest()->paginate(15);
        $tierProgress = app(\App\Services\LoyaltyService::class)->getTierProgress($customer);

        return view('portal.loyalty', compact('customer', 'balance', 'pointValue', 'ledgers', 'tierProgress'));
    }

    public function orders(Request $request)
    {
        $orders = RentalOrder::with(['vehicle', 'payments', 'invoices'])
            ->where('customer_id', $request->user('customer')->id)->latest()->paginate(15);
        return view('portal.orders', compact('orders'));
    }

    public function subscriptions(Request $request)
    {
        $subscriptions = \App\Models\Subscription::with('vehicle')
            ->where('customer_id', $request->user('customer')->id)
            ->latest()->get();
        return view('portal.subscriptions', compact('subscriptions'));
    }

    public function inspections(Request $request)
    {
        $customer = $request->user('customer');
        $orderIds = RentalOrder::where('customer_id', $customer->id)->pluck('id');
        $inspections = \App\Models\VehicleInspection::with(['vehicle'])
            ->whereIn('rental_order_id', $orderIds)
            ->latest('inspected_at')
            ->paginate(10);

        return view('portal.inspections', compact('inspections'));
    }

    public function reschedule(Request $request, RentalOrder $order, \App\Services\PricingEngine $pricing)
    {
        $customer = $request->user('customer');
        abort_unless($order->customer_id === $customer->id, 404);

        abort_unless(in_array($order->status, ['draft', 'ready_for_preparation', 'preparing', 'ready_for_handover'], true), 422, 'Pesanan sudah berjalan atau selesai — tidak bisa dijadwalkan ulang.');

        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $start = \Illuminate\Support\Carbon::parse($data['start_date']);
        $end = \Illuminate\Support\Carbon::parse($data['end_date']);

        // Kendaraan harus bebas pada rentang baru (abaikan order ini sendiri).
        $conflict = RentalOrder::where('vehicle_id', $order->vehicle_id)
            ->where('id', '!=', $order->id)
            ->whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover', 'checked_out', 'active', 'extension_requested', 'return_due', 'overdue'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(fn ($qq) => $qq->where('start_date', '<=', $start)->where('end_date', '>=', $end));
            })->exists();
        abort_if($conflict, 422, 'Kendaraan tidak tersedia pada tanggal baru tersebut.');

        if ($order->vehicle->expiredDocuments($end) !== []) {
            abort(422, 'Dokumen kendaraan kedaluwarsa pada rentang tanggal baru.');
        }

        $quote = $pricing->calculateRentalPrice($order->vehicle, $start->toDateString(), $end->toDateString(), $order->rental_type);

        $oldFinal = (float) $order->final_amount;
        $newFinal = (float) $quote['total'];
        $delta = round($newFinal - $oldFinal, 2);

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $data, $quote, $newFinal, $delta) {
            $order->update([
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration_days' => (int) $quote['duration_days'],
                'daily_rate_snapshot' => $quote['effective_daily_rate'],
                'subtotal' => $quote['subtotal'],
                'tax_total' => $quote['tax_amount'],
                'final_amount' => $newFinal,
                'balance_due' => max(0, $newFinal - (float) $order->amount_paid),
            ]);

            if ($delta > 0) {
                Invoice::create([
                    'rental_order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'type' => 'additional',
                    'subtotal' => $delta,
                    'total_amount' => $delta,
                    'balance_due' => $delta,
                    'due_date' => $order->start_date,
                    'status' => 'issued',
                    'notes' => 'Selisih penjadwalan ulang pesanan '.$order->order_number,
                ]);
            }
        });

        return back()->with(
            'status',
            $delta > 0
                ? 'Jadwal diperbarui. Invoice selisih Rp '.number_format($delta, 0, ',', '.').' telah diterbitkan.'
                : 'Jadwal berhasil diperbarui.'
        );
    }

    public function invoices(Request $request)
    {
        $invoices = Invoice::where('customer_id', $request->user('customer')->id)->latest()->paginate(15);
        $paymentProviders = Provider::active()->where('type','payment')->get();
        return view('portal.invoices', compact('invoices','paymentProviders'));
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

    public function requestExtension(Request $request, RentalOrder $order, PricingEngine $pricing)
    {
        $customer=$request->user('customer'); abort_unless($order->customer_id===$customer->id,404);
        $data=$request->validate(['requested_end_date'=>['required','date','after:'.$order->end_date->toDateString()],'reason'=>['nullable','string','max:1000']]);
        abort_if(RentalExtension::where('rental_order_id',$order->id)->where('status','pending')->exists(),422,'Masih ada permintaan perpanjangan yang menunggu.');
        $quote=$pricing->calculateRentalPrice($order->vehicle,$order->end_date->toDateString(),$data['requested_end_date'],$order->rental_type);
        RentalExtension::create(['rental_order_id'=>$order->id,'customer_id'=>$customer->id,'requested_end_date'=>$data['requested_end_date'],'additional_amount'=>$quote['total'],'reason'=>$data['reason']??null]);
        $order->update(['status'=>'extension_requested']); return back()->with('status','Permintaan perpanjangan dikirim.');
    }

    public function checkoutPayment(Request $request, Invoice $invoice, PaymentGatewayService $gateway)
    {
        abort_unless($invoice->customer_id===$request->user('customer')->id,404);
        $data=$request->validate(['provider_id'=>'required|exists:providers,id']); $provider=Provider::findOrFail($data['provider_id']);
        $transaction=$gateway->create($provider,$invoice); abort_if(blank($transaction->checkout_url),422,'Provider tidak mengembalikan checkout URL.');
        return redirect()->away($transaction->checkout_url);
    }
}
