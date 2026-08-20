<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RentalOrder;
use App\Models\TrustScoreLog;
use Illuminate\Support\Facades\DB;

class TrustScoreService
{
    protected const MIN_SCORE = 0;
    protected const MAX_SCORE = 100;
    protected const DEFAULT_SCORE = 50.0;

    protected array $tierThresholds = [
        'excellent' => 80,
        'good' => 60,
        'fair' => 40,
        'poor' => 20,
        'banned' => 0,
    ];

    protected array $tierConfig = [
        'excellent' => [
            'label' => 'Excellent',
            'discount' => 10.0,
            'priority_booking' => true,
            'deposit_required' => false,
            'max_active_bookings' => 5,
            'description' => 'Pelanggan prioritas dengan diskon eksklusif',
        ],
        'good' => [
            'label' => 'Good',
            'discount' => 5.0,
            'priority_booking' => false,
            'deposit_required' => false,
            'max_active_bookings' => 3,
            'description' => 'Pelanggan terpercaya dengan sedikit keuntungan',
        ],
        'fair' => [
            'label' => 'Fair',
            'discount' => 0.0,
            'priority_booking' => false,
            'deposit_required' => true,
            'max_active_bookings' => 2,
            'description' => 'Pelanggan standar, deposit wajib',
        ],
        'poor' => [
            'label' => 'Poor',
            'discount' => 0.0,
            'priority_booking' => false,
            'deposit_required' => true,
            'max_active_bookings' => 1,
            'description' => 'Pelanggan berisiko tinggi, deposit tinggi wajib',
        ],
        'banned' => [
            'label' => 'Banned',
            'discount' => 0.0,
            'priority_booking' => false,
            'deposit_required' => true,
            'max_active_bookings' => 0,
            'description' => 'Diblokir dari pemesanan',
        ],
    ];

    public function calculateScore(int $customerId): float
    {
        $customer = Customer::withCount(['orders as total_orders' => function ($q) {
            $q->where('status', 'completed');
        }])->findOrFail($customerId);

        $totalOrders = (int) $customer->total_orders;
        $totalSpent = (float) $customer->total_spent;

        $score = self::DEFAULT_SCORE;

        if ($totalOrders > 0) {
            $orderFrequencyScore = min(20, $totalOrders * 2);
            $score += $orderFrequencyScore;
        }

        if ($totalSpent > 0) {
            $spendingScore = min(15, log10(max(1, $totalSpent)) * 3);
            $score += $spendingScore;
        }

        $completedOrders = RentalOrder::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->count();

        $totalOrdersAll = RentalOrder::where('customer_id', $customerId)->count();

        if ($totalOrdersAll > 0) {
            $completionRate = $completedOrders / $totalOrdersAll;
            $completionScore = $completionRate * 15;
            $score += $completionScore;
        }

        $onTimeReturns = RentalOrder::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->where('actual_return_date', '<=', DB::raw('end_date'))
            ->count();

        if ($completedOrders > 0) {
            $punctualityScore = ($onTimeReturns / $completedOrders) * 15;
            $score += $punctualityScore;
        }

        $damageReports = \App\Models\DamageReport::where('customer_id', $customerId)
            ->count();
        $damagePenalty = min(20, $damageReports * 5);
        $score -= $damagePenalty;

        $isBlacklisted = $customer->isBlacklisted();
        if ($isBlacklisted) {
            $score -= 30;
        }

        $cancellations = RentalOrder::where('customer_id', $customerId)
            ->where('status', 'cancelled')
            ->count();
        $cancellationPenalty = min(15, $cancellations * 5);
        $score -= $cancellationPenalty;

        return (float) max(self::MIN_SCORE, min(self::MAX_SCORE, round($score, 2)));
    }

    public function updateScore(int $customerId, float $change, string $reason, ?string $referenceType = null, ?int $referenceId = null): Customer
    {
        $customer = Customer::findOrFail($customerId);
        $previousScore = (float) $customer->trust_score;

        $newScore = max(self::MIN_SCORE, min(self::MAX_SCORE, $previousScore + $change));
        $newScore = round($newScore, 2);

        $customer->update(['trust_score' => $newScore]);

        TrustScoreLog::create([
            'customer_id' => $customerId,
            'previous_score' => $previousScore,
            'new_score' => $newScore,
            'change_amount' => round($change, 2),
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        return $customer->fresh();
    }

    public function getScoreLevel(float $score): string
    {
        if ($score >= $this->tierThresholds['excellent']) {
            return 'excellent';
        }
        if ($score >= $this->tierThresholds['good']) {
            return 'good';
        }
        if ($score >= $this->tierThresholds['fair']) {
            return 'fair';
        }
        if ($score >= $this->tierThresholds['poor']) {
            return 'poor';
        }
        return 'banned';
    }

    public function getTierConfig(string $tier): array
    {
        return $this->tierConfig[$tier] ?? $this->tierConfig['fair'];
    }

    public function getAllTiers(): array
    {
        return $this->tierConfig;
    }

    public function getCustomerTierInfo(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        $score = (float) $customer->trust_score;
        $level = $this->getScoreLevel($score);
        $config = $this->getTierConfig($level);

        return [
            'customer_id' => $customerId,
            'score' => $score,
            'level' => $level,
            'label' => $config['label'],
            'discount' => $config['discount'],
            'priority_booking' => $config['priority_booking'],
            'deposit_required' => $config['deposit_required'],
            'max_active_bookings' => $config['max_active_bookings'],
            'description' => $config['description'],
        ];
    }

    public function recalculateAllScores(): int
    {
        $customerIds = Customer::where('is_active', true)
            ->pluck('id')
            ->toArray();

        $updated = 0;

        foreach ($customerIds as $customerId) {
            $newScore = $this->calculateScore($customerId);
            $customer = Customer::find($customerId);

            if ($customer && (float) $customer->trust_score !== $newScore) {
                $this->updateScore($customerId, $newScore - (float) $customer->trust_score, 'System recalculation');
                $updated++;
            }
        }

        return $updated;
    }
}
