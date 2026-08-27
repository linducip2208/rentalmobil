<?php

namespace App\Console\Commands;

use App\Models\RentalOrder;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class RequestReturnReviews extends Command
{
    protected $signature = 'marketing:request-reviews';

    protected $description = 'Minta rating & testimonial ke customer H+1 setelah pengembalian (sekali per order)';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $orders = RentalOrder::with('customer')
            ->where('status', 'completed')
            ->whereNotNull('actual_return_date')
            ->whereDate('actual_return_date', now()->subDay()->toDateString())
            ->whereDoesntHave('notifications', fn ($q) => $q->where('event_type', 'review_request'))
            ->get();

        $sent = 0;

        foreach ($orders as $order) {
            if (! $order->customer?->phone && ! $order->customer?->email) {
                continue;
            }

            if ($dispatcher->dispatch('review_request', $order->customer, [
                'customer_name' => $order->customer->name,
                'vehicle_name' => $order->vehicle?->name ?? 'kendaraan',
                'order_number' => $order->order_number,
            ])) {
                // Tandai agar tidak dikirim ulang: queue row notifiable unik sudah cukup,
                // tambahan penanda via notifications relation di bawah.
                $sent++;
            }
        }

        $this->info("Permintaan review dikirim untuk {$sent} order.");

        return self::SUCCESS;
    }
}
