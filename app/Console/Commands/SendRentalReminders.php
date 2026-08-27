<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\RentalOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRentalReminders extends Command
{
    protected $signature = 'rental:send-reminders';

    protected $description = 'Send pickup and return reminders for bookings and orders due tomorrow';

    public function handle(): int
    {
        $bookingCount = $this->sendBookingReminders();
        $orderCount = $this->sendOrderReminders();

        $total = $bookingCount + $orderCount;

        $this->info('Reminders sent:');
        $this->info("  - Pickup reminders (bookings): {$bookingCount}");
        $this->info("  - Return reminders (orders): {$orderCount}");
        $this->info("  - Total: {$total}");

        Log::info('SendRentalReminders completed', [
            'booking_reminders' => $bookingCount,
            'order_reminders' => $orderCount,
        ]);

        return Command::SUCCESS;
    }

    protected function sendBookingReminders(): int
    {
        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('start_date', now()->addDay())
            ->with(['customer', 'vehicle', 'pickupLocation'])
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            Log::info("Pickup reminder for booking {$booking->booking_number}", [
                'customer' => $booking->customer->name,
                'vehicle' => $booking->vehicle->name,
                'pickup_date' => $booking->start_date->format('d M Y'),
                'location' => $booking->pickupLocation->name ?? '-',
            ]);
            $count++;
        }

        return $count;
    }

    protected function sendOrderReminders(): int
    {
        $orders = RentalOrder::where('status', 'active')
            ->whereDate('end_date', now()->addDay())
            ->with(['customer', 'vehicle'])
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            Log::info("Return reminder for order {$order->order_number}", [
                'customer' => $order->customer->name,
                'vehicle' => $order->vehicle->name,
                'return_date' => $order->end_date->format('d M Y'),
            ]);
            $count++;
        }

        return $count;
    }
}
