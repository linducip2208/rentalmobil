<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\RentalOrder;
use App\Services\DepositRefundService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = RentalOrder::query()
            ->with(['vehicle', 'customer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('order_number', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('lte.orders.index', [
            'orders' => $orders,
            'statuses' => [
                'draft' => 'Draft', 'ready_for_preparation' => 'Siap Persiapan', 'preparing' => 'Disiapkan',
                'ready_for_handover' => 'Siap Serah Terima', 'checked_out' => 'Sudah Keluar', 'active' => 'Aktif',
                'extension_requested' => 'Minta Perpanjangan', 'return_due' => 'Jatuh Tempo Kembali',
                'overdue' => 'Terlambat', 'return_inspection' => 'Inspeksi Kembali', 'payment_pending' => 'Pembayaran Tertunda',
                'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'disputed' => 'Sengketa',
            ],
        ]);
    }

    public function show(RentalOrder $order): View
    {
        return view('lte.orders.show', [
            'order' => $order->load(['vehicle.category', 'customer', 'driver', 'location', 'payments', 'deposits']),
        ]);
    }

    public function refundDeposit(Request $request, RentalOrder $order, Deposit $deposit, DepositRefundService $service)
    {
        abort_unless($deposit->rental_order_id === $order->id, 404);

        $data = $request->validate([
            'fuel' => ['nullable', 'numeric', 'min:0'],
            'damage' => ['nullable', 'numeric', 'min:0'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'cleaning' => ['nullable', 'numeric', 'min:0'],
        ]);

        $deductions = array_filter([
            'fuel' => (float) ($data['fuel'] ?? 0),
            'damage' => (float) ($data['damage'] ?? 0),
            'late_fee' => (float) ($data['late_fee'] ?? 0),
            'cleaning' => (float) ($data['cleaning'] ?? 0),
        ], fn ($v) => $v > 0);

        try {
            $service->refund($deposit, $deductions, auth()->id());

            return back()->with('status', 'Deposit direfund: Rp '.number_format((float) $deposit->fresh()->refund_amount, 0, ',', '.').'. Jurnal keuangan otomatis.');
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }
}
