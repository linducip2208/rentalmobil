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
