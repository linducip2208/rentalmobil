<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\RentalOrder;

class OrderController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->customer;

        $status = request('status');
        $query = $customer->orders()->with(['vehicle', 'payments']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('portal.orders.index', compact('orders', 'status'));
    }

    public function show(RentalOrder $order)
    {
        $customer = auth('customer')->user()->customer;

        abort_if($order->customer_id !== $customer->id, 403);

        $order->load(['vehicle', 'driver', 'items.addon', 'payments.paymentMethod', 'returns', 'booking']);

        $timeline = $this->buildTimeline($order);

        return view('portal.orders.show', compact('order', 'timeline'));
    }

    protected function buildTimeline(RentalOrder $order): array
    {
        $events = [];

        $events[] = [
            'label' => 'Pesanan Dibuat',
            'date' => $order->created_at,
            'done' => true,
        ];

        if ($order->status !== 'pending') {
            $events[] = [
                'label' => 'Pesanan Dikonfirmasi',
                'date' => $order->dispatched_at ?? $order->created_at,
                'done' => true,
            ];
        }

        if (in_array($order->status, ['active', 'return_pending', 'returned', 'completed'])) {
            $events[] = [
                'label' => 'Mobil Diambil',
                'date' => $order->checked_out_at,
                'done' => true,
            ];
        }

        if (in_array($order->status, ['returned', 'completed'])) {
            $events[] = [
                'label' => 'Mobil Dikembalikan',
                'date' => $order->checked_in_at,
                'done' => true,
            ];
        }

        if ($order->status === 'completed') {
            $events[] = [
                'label' => 'Selesai',
                'date' => $order->completed_at,
                'done' => true,
            ];
        }

        if ($order->status === 'cancelled') {
            $events[] = [
                'label' => 'Dibatalkan',
                'date' => $order->cancelled_at,
                'done' => false,
            ];
        }

        return $events;
    }
}
