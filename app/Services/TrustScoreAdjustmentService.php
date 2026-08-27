<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SystemSetting;
use App\Models\TrustScoreLog;
use Illuminate\Support\Facades\DB;

/**
 * Trust score dinamis: skor customer naik/turun mengikuti event
 * (order selesai, telat kembali, kerusakan, face verification, dsb.).
 * Bobot event diatur pemilik via SystemSetting "trust_score_events" (JSON).
 */
class TrustScoreAdjustmentService
{
    public const DEFAULT_WEIGHTS = [
        'order_completed' => 2,
        'return_on_time' => 3,
        'return_late' => -5,
        'damage_reported' => -10,
        'payment_late' => -4,
        'face_verified' => 5,
        'face_failed' => -15,
        'fraud_hit' => -20,
        'insurance_claim' => -8,
    ];

    public function adjust(Customer $customer, int $delta, string $reason): Customer
    {
        return DB::transaction(function () use ($customer, $delta, $reason) {
            $old = (int) $customer->trust_score;
            $new = max(0, min(100, $old + $delta));

            $customer->update(['trust_score' => $new]);

            if ($new !== $old) {
                TrustScoreLog::create([
                    'customer_id' => $customer->id,
                    'previous_score' => $old,
                    'new_score' => $new,
                    'change_reason' => $reason,
                    'changed_by' => null,
                ]);
            }

            return $customer->refresh();
        });
    }

    public function adjustFromEvent(Customer $customer, string $eventType): ?Customer
    {
        $weights = $this->weights();

        if (! isset($weights[$eventType])) {
            return null;
        }

        return $this->adjust($customer, (int) $weights[$eventType], ucfirst(str_replace('_', ' ', $eventType)));
    }

    protected function weights(): array
    {
        $raw = SystemSetting::get('trust_score_events');

        if (! $raw) {
            return self::DEFAULT_WEIGHTS;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        return is_array($decoded) && ! empty($decoded) ? $decoded : self::DEFAULT_WEIGHTS;
    }
}
