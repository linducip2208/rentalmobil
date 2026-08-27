<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FraudHit;
use App\Models\FraudPattern;
use Illuminate\Support\Facades\DB;

/**
 * Pemindaian pola fraud:
 * - duplicate_document: KTP/SIM sama dipakai banyak akun
 * - shared_contact: nomor HP/email dipakai bersama akun berbeda
 * - ip_cluster: fingerprint/IP sama pada banyak customer
 * - booking_velocity: jumlah booking abnormal dalam jendela waktu
 */
class FraudPatternDetectionService
{
    public function scan(): array
    {
        $results = ['patterns' => 0, 'hits_created' => 0];

        foreach (FraudPattern::active()->get() as $pattern) {
            $results['patterns']++;
            $hits = match ($pattern->pattern_type) {
                'duplicate_document' => $this->scanDuplicateDocuments($pattern),
                'shared_contact' => $this->scanSharedContact($pattern),
                'ip_cluster' => $this->scanIpCluster($pattern),
                'booking_velocity' => $this->scanBookingVelocity($pattern),
                default => collect(),
            };

            foreach ($hits as $hit) {
                FraudHit::create($hit + ['fraud_pattern_id' => $pattern->id, 'status' => 'new']);
                $results['hits_created']++;
            }
        }

        return $results;
    }

    protected function scanDuplicateDocuments(FraudPattern $pattern): \Illuminate\Support\Collection
    {
        $minAccounts = (int) ($pattern->conditions['min_accounts'] ?? 2);

        return DB::table('customers')
            ->whereNotNull('ktp_number')
            ->where('ktp_number', '!=', '')
            ->select('ktp_number', DB::raw('COUNT(*) as account_count'), DB::raw("GROUP_CONCAT(id) as customer_ids"))
            ->groupBy('ktp_number')
            ->havingRaw('COUNT(*) >= ?', [$minAccounts])
            ->get()
            ->map(fn ($row) => [
                'customer_id' => null,
                'severity' => min(5, $row->account_count),
                'details' => [
                    'ktp_number' => substr($row->ktp_number, 0, 4) . '****',
                    'account_count' => $row->account_count,
                    'customer_ids' => array_map('intval', explode(',', $row->customer_ids)),
                ],
            ]);
    }

    protected function scanSharedContact(FraudPattern $pattern): \Illuminate\Support\Collection
    {
        $minAccounts = (int) ($pattern->conditions['min_accounts'] ?? 3);
        $lookback = now()->subDays($pattern->lookback_days);

        $phones = DB::table('customers')
            ->where('is_active', true)
            ->where('created_at', '>=', $lookback)
            ->select('phone', DB::raw('COUNT(*) as cnt'), DB::raw("GROUP_CONCAT(id) as ids"))
            ->groupBy('phone')
            ->havingRaw('COUNT(*) >= ?', [$minAccounts])
            ->get();

        return $phones
            ->filter(fn ($row) => filled($row->phone))
            ->map(fn ($row) => [
                'severity' => min(5, $row->cnt),
                'details' => [
                    'type' => 'phone',
                    'contact_masked' => substr($row->phone, 0, 5) . '****',
                    'account_count' => $row->cnt,
                    'customer_ids' => array_map('intval', explode(',', $row->ids)),
                ],
            ]);
    }

    protected function scanIpCluster(FraudPattern $pattern): \Illuminate\Support\Collection
    {
        $minCustomers = (int) ($pattern->conditions['min_customers'] ?? 3);
        $lookback = now()->subDays($pattern->lookback_days);

        $clusters = DB::table('risk_assessments')
            ->where('created_at', '>=', $lookback)
            ->whereNotNull('fingerprint_hash')
            ->select('fingerprint_hash', DB::raw('COUNT(DISTINCT customer_id) as cnt'), DB::raw('GROUP_CONCAT(DISTINCT customer_id) as ids'))
            ->groupBy('fingerprint_hash')
            ->havingRaw('COUNT(DISTINCT customer_id) >= ?', [$minCustomers])
            ->get();

        return $clusters->map(fn ($row) => [
            'severity' => min(5, $row->cnt),
            'details' => [
                'fingerprint_hash' => substr($row->fingerprint_hash, 0, 12) . '…',
                'distinct_customers' => $row->cnt,
                'customer_ids' => array_map('intval', array_filter(explode(',', $row->ids))),
            ],
        ]);
    }

    protected function scanBookingVelocity(FraudPattern $pattern): \Illuminate\Support\Collection
    {
        $maxBookings = (int) ($pattern->conditions['max_bookings_per_day'] ?? 5);
        $windowDays = max(1, intdiv((int) $pattern->lookback_days, 30));
        $from = now()->subDays($windowDays);

        $velocity = DB::table('bookings')
            ->where('created_at', '>=', $from)
            ->select('customer_id', DB::raw('COUNT(*) as booking_count'))
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > ?', [$maxBookings])
            ->orderByDesc('booking_count')
            ->limit(50)
            ->get();

        return $velocity->map(fn ($row) => [
            'customer_id' => $row->customer_id,
            'severity' => min(5, intdiv($row->booking_count, $maxBookings)),
            'subject_type' => \App\Models\Booking::class,
            'details' => [
                'window_days' => $windowDays,
                'booking_count' => $row->booking_count,
                'threshold' => $maxBookings,
            ],
        ]);
    }
}
