<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\RentalOrder;
use Filament\Pages\Page;

class ConvertBookingToOrder extends Page
{
    protected string $view = 'filament.pages.convert-booking';

    protected static ?string $title = 'Konversi Booking ke Order';

    public ?int $bookingId = null;
    public ?array $booking = null;
    public bool $confirmed = false;
    public ?int $driverId = null;
    public ?int $pickupKm = null;
    public string $notes = '';

    public function mount(int $booking): void
    {
        $this->bookingId = $booking;

        $bookingModel = Booking::with(['customer', 'vehicle', 'driver', 'pickupLocation', 'returnLocation'])
            ->find($booking);

        if (! $bookingModel) {
            abort(404);
        }

        $this->booking = [
            'id' => $bookingModel->id,
            'booking_number' => $bookingModel->booking_number,
            'customer_name' => $bookingModel->customer?->name ?? '-',
            'customer_phone' => $bookingModel->customer?->phone ?? '-',
            'vehicle_name' => $bookingModel->vehicle?->name ?? '-',
            'vehicle_plate' => $bookingModel->vehicle?->plate_number ?? '-',
            'start_date' => $bookingModel->start_date?->format('d M Y H:i'),
            'end_date' => $bookingModel->end_date?->format('d M Y H:i'),
            'duration_days' => $bookingModel->duration_days,
            'daily_rate' => $bookingModel->daily_rate_snapshot,
            'subtotal' => $bookingModel->subtotal,
            'discount_amount' => $bookingModel->discount_amount,
            'tax_amount' => $bookingModel->tax_amount,
            'total_amount' => $bookingModel->total_amount,
            'deposit_amount' => $bookingModel->deposit_amount,
            'status' => $bookingModel->status,
            'pickup_location' => $bookingModel->pickupLocation?->name ?? '-',
            'return_location' => $bookingModel->returnLocation?->name ?? '-',
            'driver_name' => $bookingModel->driver?->name ?? 'Tanpa supir',
            'notes' => $bookingModel->notes,
        ];
    }

    public function getDriversProperty(): array
    {
        return Driver::where('is_available', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function convert(): void
    {
        $booking = Booking::with(['customer', 'vehicle', 'pickupLocation', 'returnLocation'])
            ->find($this->bookingId);

        if (! $booking || $booking->status !== 'confirmed') {
            session()->flash('error', 'Booking tidak valid atau status sudah berubah.');

            return;
        }

        $order = RentalOrder::create([
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'vehicle_id' => $booking->vehicle_id,
            'driver_id' => $this->driverId,
            'location_id' => $booking->pickup_location_id,
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
            'rental_type' => $booking->rental_type,
            'duration_days' => $booking->duration_days,
            'daily_rate_snapshot' => $booking->daily_rate_snapshot,
            'subtotal' => $booking->subtotal,
            'discount_total' => $booking->discount_amount,
            'tax_total' => $booking->tax_amount,
            'final_amount' => $booking->total_amount,
            'deposit_amount' => $booking->deposit_amount,
            'balance_due' => $booking->total_amount - $booking->deposit_amount,
            'pickup_km' => $this->pickupKm,
            'notes' => $this->notes,
            'status' => 'draft',
            'payment_status' => $booking->deposit_amount > 0 ? 'partial' : 'unpaid',
            'created_by' => auth()->id(),
        ]);

        $booking->update([
            'status' => 'converted',
        ]);

        $this->confirmed = true;

        session()->flash('success', "Berhasil dikonversi! Order #{$order->order_number} telah dibuat.");
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->booking['subtotal'] ?? 0);
    }

    public function getDiscountAttribute(): float
    {
        return (float) ($this->booking['discount_amount'] ?? 0);
    }

    public function getTaxAttribute(): float
    {
        return (float) ($this->booking['tax_amount'] ?? 0);
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->booking['total_amount'] ?? 0);
    }
}
