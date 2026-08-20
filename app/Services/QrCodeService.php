<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    protected string $disk = 'public';
    protected string $directory = 'qr-codes';

    public function generateUnitQr(Vehicle $vehicle): string
    {
        $payload = json_encode([
            'type' => 'vehicle',
            'id' => $vehicle->id,
            'plate' => $vehicle->license_plate,
            'name' => $vehicle->name,
            'location_id' => $vehicle->location_id,
        ]);

        $filename = "vehicle-{$vehicle->id}-" . time() . ".svg";
        $path = "{$this->directory}/vehicles/{$filename}";

        $qrCode = QrCode::size(300)
            ->generate($payload);

        Storage::disk($this->disk)->put($path, $qrCode);

        return $path;
    }

    public function generateBookingQr(Booking $booking): string
    {
        $payload = json_encode([
            'type' => 'booking',
            'id' => $booking->id,
            'number' => $booking->booking_number,
            'customer_id' => $booking->customer_id,
            'vehicle_id' => $booking->vehicle_id,
        ]);

        $filename = "booking-{$booking->id}-" . time() . ".svg";
        $path = "{$this->directory}/bookings/{$filename}";

        $qrCode = QrCode::size(300)
            ->generate($payload);

        Storage::disk($this->disk)->put($path, $qrCode);

        $booking->update(['qr_code_path' => $path]);

        return $path;
    }

    public function generateOrderQr(\App\Models\RentalOrder $order): string
    {
        $payload = json_encode([
            'type' => 'order',
            'id' => $order->id,
            'number' => $order->order_number,
            'customer_id' => $order->customer_id,
            'vehicle_id' => $order->vehicle_id,
            'status' => $order->status,
        ]);

        $filename = "order-{$order->id}-" . time() . ".svg";
        $path = "{$this->directory}/orders/{$filename}";

        $qrCode = QrCode::size(300)
            ->generate($payload);

        Storage::disk($this->disk)->put($path, $qrCode);

        return $path;
    }

    public function generateReturnQr(\App\Models\ReturnRecord $returnRecord): string
    {
        $payload = json_encode([
            'type' => 'return',
            'id' => $returnRecord->id,
            'order_id' => $returnRecord->rental_order_id,
        ]);

        $filename = "return-{$returnRecord->id}-" . time() . ".svg";
        $path = "{$this->directory}/returns/{$filename}";

        $qrCode = QrCode::size(300)
            ->generate($payload);

        Storage::disk($this->disk)->put($path, $qrCode);

        return $path;
    }

    public function decodeQr(string $content): ?array
    {
        $data = json_decode($content, true);

        if (!$data || !isset($data['type'], $data['id'])) {
            return null;
        }

        return $data;
    }

    public function validateBookingQr(string $content, int $bookingId): bool
    {
        $data = $this->decodeQr($content);

        if (!$data || $data['type'] !== 'booking') {
            return false;
        }

        return (int) $data['id'] === $bookingId;
    }

    public function getQrUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function deleteQr(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }
        return false;
    }
}
