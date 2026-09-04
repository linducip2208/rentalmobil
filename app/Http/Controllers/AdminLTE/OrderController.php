<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\RentalOrder;
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
            'order' => $order->load(['vehicle.category', 'customer', 'driver', 'location', 'payments', 'deposits', 'location']),
        ]);
    }
}
