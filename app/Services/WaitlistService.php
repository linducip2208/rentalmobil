<?php

namespace App\Services;

use App\Models\BookingWaitlist;
use Illuminate\Support\Facades\DB;

class WaitlistService
{
    public function offer(BookingWaitlist $waitlist, int $validHours = 6): BookingWaitlist
    {
        if ($waitlist->status !== 'waiting') {
            throw new \RuntimeException('Hanya daftar tunggu berstatus menunggu yang dapat ditawarkan.');
        }

        return DB::transaction(function () use ($waitlist, $validHours) {
            $waitlist->update(['status' => 'offered', 'offered_at' => now(), 'expires_at' => now()->addHours($validHours)]);
            app(NotificationDispatcher::class)->dispatch('waitlist_offer', $waitlist->customer, [
                'customer_name' => $waitlist->customer->name,
                'category_name' => $waitlist->category?->name ?? 'kendaraan pilihan',
                'start_date' => $waitlist->start_date->translatedFormat('d M Y'),
                'end_date' => $waitlist->end_date->translatedFormat('d M Y'),
                'expires_at' => $waitlist->expires_at->translatedFormat('d M Y H:i'),
            ]);

            return $waitlist->fresh();
        });
    }
}
