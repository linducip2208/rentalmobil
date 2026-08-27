<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Hold unit sementara (15-30 menit) saat proses checkout agar tidak double-booked.
 */
class BookingHoldService
{
    public const DEFAULT_MINUTES = 20;

    public function createHold(Vehicle $vehicle, string $startDate, string $endDate, ?string $sessionId = null, int $minutes = self::DEFAULT_MINUTES): BookingHold
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return DB::transaction(function () use ($vehicle, $start, $end, $sessionId, $minutes) {
            $conflict = BookingHold::where('vehicle_id', $vehicle->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->whereDate('start_date', '<=', $end)
                ->whereDate('end_date', '>=', $start)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new \RuntimeException('Unit sedang di-hold pemesan lain. Coba beberapa menit lagi.');
            }

            return BookingHold::create([
                'vehicle_id' => $vehicle->id,
                'hold_token' => (string) Str::uuid(),
                'session_id' => $sessionId,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'expires_at' => now()->addMinutes($minutes),
                'status' => 'active',
            ]);
        });
    }

    public function extend(BookingHold $hold, int $minutes = self::DEFAULT_MINUTES): BookingHold
    {
        abort_unless($hold->status === 'active', 422, 'Hold sudah tidak aktif.');

        $hold->update(['expires_at' => now()->addMinutes($minutes)]);

        return $hold;
    }

    public function release(BookingHold $hold): void
    {
        $hold->update(['status' => 'released']);
    }

    public function convertToBooking(BookingHold $hold, Booking $booking): void
    {
        $hold->update([
            'status' => 'converted',
            'booking_id' => $booking->id,
        ]);
    }

    public function expireStale(): int
    {
        return BookingHold::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    public function hasActiveConflict(int $vehicleId, string $startDate, string $endDate): bool
    {
        return BookingHold::where('vehicle_id', $vehicleId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
    }
}
