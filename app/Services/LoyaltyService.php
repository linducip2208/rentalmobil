<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RentalOrder;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    protected array $tierThresholds = [
        'bronze' => [
            'min_orders' => 0,
            'min_spent' => 0,
        ],
        'silver' => [
            'min_orders' => 5,
            'min_spent' => 5000000,
        ],
        'gold' => [
            'min_orders' => 15,
            'min_spent' => 20000000,
        ],
        'platinum' => [
            'min_orders' => 30,
            'min_spent' => 50000000,
        ],
        'diamond' => [
            'min_orders' => 50,
            'min_spent' => 100000000,
        ],
    ];

    protected array $tierBenefits = [
        'bronze' => [
            'label' => 'Bronze',
            'discount_percentage' => 0,
            'priority_booking' => false,
            'free_driver' => false,
            'free_insurance' => false,
            'extended_return_hours' => 0,
            'points_multiplier' => 1.0,
            'description' => 'Pelanggan baru',
        ],
        'silver' => [
            'label' => 'Silver',
            'discount_percentage' => 3,
            'priority_booking' => false,
            'free_driver' => false,
            'free_insurance' => false,
            'extended_return_hours' => 2,
            'points_multiplier' => 1.2,
            'description' => 'Diskon 3%, perpanjangan pengembalian 2 jam',
        ],
        'gold' => [
            'label' => 'Gold',
            'discount_percentage' => 5,
            'priority_booking' => true,
            'free_driver' => false,
            'free_insurance' => true,
            'extended_return_hours' => 4,
            'points_multiplier' => 1.5,
            'description' => 'Diskon 5%, prioritas booking, asuransi gratis',
        ],
        'platinum' => [
            'label' => 'Platinum',
            'discount_percentage' => 8,
            'priority_booking' => true,
            'free_driver' => true,
            'free_insurance' => true,
            'extended_return_hours' => 6,
            'points_multiplier' => 2.0,
            'description' => 'Diskon 8%, supir gratis, asuransi gratis',
        ],
        'diamond' => [
            'label' => 'Diamond',
            'discount_percentage' => 12,
            'priority_booking' => true,
            'free_driver' => true,
            'free_insurance' => true,
            'extended_return_hours' => 12,
            'points_multiplier' => 3.0,
            'description' => 'Diskon 12%, semua fasilitas premium',
        ],
    ];

    public function calculateTier(Customer $customer): string
    {
        $totalOrders = $customer->total_orders ?? 0;
        $totalSpent = (float) ($customer->total_spent ?? 0);

        $tier = 'bronze';

        foreach ($this->tierThresholds as $tierName => $thresholds) {
            if ($totalOrders >= $thresholds['min_orders'] && $totalSpent >= $thresholds['min_spent']) {
                $tier = $tierName;
            }
        }

        return $tier;
    }

    public function getTierBenefits(string $tier): array
    {
        return $this->tierBenefits[$tier] ?? $this->tierBenefits['bronze'];
    }

    public function getAllTiers(): array
    {
        return $this->tierBenefits;
    }

    public function updateTier(Customer $customer): array
    {
        $newTier = $this->calculateTier($customer);
        $currentTier = $customer->loyalty_tier ?? 'bronze';
        $benefits = $this->getTierBenefits($newTier);

        $tierChanged = $currentTier !== $newTier;

        if ($tierChanged) {
            $customer->update(['loyalty_tier' => $newTier]);

            if ($tierChanged) {
                $tierOrder = array_keys($this->tierBenefits);
                $currentIndex = array_search($currentTier, $tierOrder);
                $newIndex = array_search($newTier, $tierOrder);

                $direction = $newIndex > $currentIndex ? 'upgraded' : 'downgraded';

                app(NotificationDispatcher::class)->dispatch(
                    'loyalty_tier_changed',
                    $customer,
                    [
                        'previous_tier' => $currentTier,
                        'new_tier' => $newTier,
                        'direction' => $direction,
                        'benefits' => $benefits,
                    ]
                );
            }
        }

        return [
            'customer_id' => $customer->id,
            'previous_tier' => $currentTier,
            'current_tier' => $newTier,
            'tier_changed' => $tierChanged,
            'benefits' => $benefits,
            'total_orders' => $customer->total_orders ?? 0,
            'total_spent' => (float) ($customer->total_spent ?? 0),
        ];
    }

    public function getTierProgress(Customer $customer): array
    {
        $currentTier = $customer->loyalty_tier ?? 'bronze';
        $totalOrders = $customer->total_orders ?? 0;
        $totalSpent = (float) ($customer->total_spent ?? 0);

        $tierOrder = array_keys($this->tierBenefits);
        $currentIndex = array_search($currentTier, $tierOrder);
        $nextTierIndex = $currentIndex + 1;

        if ($nextTierIndex >= count($tierOrder)) {
            return [
                'current_tier' => $currentTier,
                'next_tier' => null,
                'progress' => 100,
                'orders_needed' => 0,
                'spent_needed' => 0,
            ];
        }

        $nextTier = $tierOrder[$nextTierIndex];
        $nextThresholds = $this->tierThresholds[$nextTier];

        $ordersProgress = $nextThresholds['min_orders'] > 0
            ? min(100, ($totalOrders / $nextThresholds['min_orders']) * 100)
            : 100;

        $spentProgress = $nextThresholds['min_spent'] > 0
            ? min(100, ($totalSpent / $nextThresholds['min_spent']) * 100)
            : 100;

        $overallProgress = round(min($ordersProgress, $spentProgress), 1);

        return [
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress' => $overallProgress,
            'orders_needed' => max(0, $nextThresholds['min_orders'] - $totalOrders),
            'spent_needed' => max(0, $nextThresholds['min_spent'] - $totalSpent),
        ];
    }

    public function calculatePoints(float $amount, string $tier): int
    {
        $benefits = $this->getTierBenefits($tier);
        $multiplier = $benefits['points_multiplier'] ?? 1.0;
        return (int) floor($amount / 10000 * $multiplier);
    }

    public function recalculateAllTiers(): array
    {
        $customers = Customer::where('is_active', true)->get();
        $results = ['updated' => 0, 'unchanged' => 0];

        foreach ($customers as $customer) {
            $result = $this->updateTier($customer);
            if ($result['tier_changed']) {
                $results['updated']++;
            } else {
                $results['unchanged']++;
            }
        }

        return $results;
    }
}
