<?php

namespace App\Services;

use App\Models\AbandonedBooking;
use App\Models\Booking;
use App\Models\ExternalReview;
use Illuminate\Support\Collection;

/**
 * Pemulihan booking terbengkalai: capture quote yang tidak lanjut,
 * kirim reminder bertahap, tandai recovered saat customer kembali.
 */
class AbandonedBookingRecoveryService
{
    public function capture(string $sessionId, array $data): AbandonedBooking
    {
        $existing = AbandonedBooking::where('session_id', $sessionId)->open()->first();

        if ($existing) {
            $existing->update($data + ['last_activity_at' => now()]);
            return $existing->refresh();
        }

        return AbandonedBooking::create($data + [
            'session_id' => $sessionId,
            'status' => 'open',
            'last_activity_at' => now(),
        ]);
    }

    public function markRecovered(AbandonedBooking $abandoned, Booking $booking): void
    {
        $abandoned->update([
            'status' => 'recovered',
            'recovered_booking_id' => $booking->id,
        ]);
    }

    public function sendReminders(): array
    {
        $sent = 0;
        $expired = 0;
        $maxReminders = (int) \App\Models\SystemSetting::get('abandoned_max_reminders', 2);
        $staleHours = 2;

        // Expire lead lama (>7 hari tanpa aktivitas).
        $expired = AbandonedBooking::open()
            ->where('last_activity_at', '<', now()->subDays(7))
            ->update(['status' => 'expired']);

        $candidates = AbandonedBooking::open()
            ->stale($staleHours)
            ->where('reminders_sent', '<', $maxReminders)
            ->where(fn ($q) => $q->whereNotNull('phone')->orWhereNotNull('email'))
            ->get();

        foreach ($candidates as $candidate) {
            app(NotificationDispatcher::class)->dispatch('abandoned_booking_recovery', $candidate, [
                'vehicle_name' => $candidate->vehicle?->name,
                'total' => data_get($candidate->quote_snapshot, 'total'),
                'start_date' => data_get($candidate->quote_snapshot, 'start_date'),
                'end_date' => data_get($candidate->quote_snapshot, 'end_date'),
                'booking_url' => config('app.url') . '/booking?vehicle_id=' . $candidate->vehicle_id,
            ]);

            $candidate->increment('reminders_sent');
            $sent++;
        }

        return ['reminders_sent' => $sent, 'expired' => $expired];
    }

    /**
     * Import ulasan eksternal dari CSV: platform,author,rating,content,date
     */
    public function importReviewsCsv(string $csvContent, string $batch): array
    {
        $rows = array_filter(array_map('trim', explode("\n", $csvContent)));
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0 && str_contains(strtolower($row), 'platform')) {
                continue;
            }

            $cols = str_getcsv($row);

            if (count($cols) < 3) {
                continue;
            }

            ExternalReview::create([
                'platform' => in_array(strtolower($cols[0]), ['google', 'maps', 'tripadvisor', 'whatsapp', 'manual']) ? strtolower($cols[0]) : 'manual',
                'author_name' => trim($cols[1]),
                'rating' => min(5, max(1, (int) $cols[2])),
                'content' => $cols[3] ?? null,
                'review_date' => $cols[4] ?? now()->toDateString(),
                'import_batch' => $batch,
            ]);

            $imported++;
        }

        return ['imported' => $imported, 'batch' => $batch];
    }
}
