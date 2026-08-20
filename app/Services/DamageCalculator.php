<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\SystemSetting;

class DamageCalculator
{
    protected array $costMatrix = [
        'scratch' => [
            'minor' => 500000,
            'moderate' => 1500000,
            'severe' => 3000000,
            'critical' => 5000000,
        ],
        'dent' => [
            'minor' => 800000,
            'moderate' => 2500000,
            'severe' => 5000000,
            'critical' => 8000000,
        ],
        'crack' => [
            'minor' => 300000,
            'moderate' => 1000000,
            'severe' => 2500000,
            'critical' => 4000000,
        ],
        'broken_part' => [
            'minor' => 1000000,
            'moderate' => 3000000,
            'severe' => 7000000,
            'critical' => 15000000,
        ],
        'paint_damage' => [
            'minor' => 600000,
            'moderate' => 2000000,
            'severe' => 4500000,
            'critical' => 8000000,
        ],
        'interior_damage' => [
            'minor' => 400000,
            'moderate' => 1500000,
            'severe' => 3500000,
            'critical' => 6000000,
        ],
        'tire_damage' => [
            'minor' => 500000,
            'moderate' => 1500000,
            'severe' => 3000000,
            'critical' => 5000000,
        ],
        'glass_damage' => [
            'minor' => 750000,
            'moderate' => 2000000,
            'severe' => 5000000,
            'critical' => 10000000,
        ],
        'mechanical' => [
            'minor' => 1000000,
            'moderate' => 3000000,
            'severe' => 8000000,
            'critical' => 15000000,
        ],
        'accident' => [
            'minor' => 2000000,
            'moderate' => 5000000,
            'severe' => 15000000,
            'critical' => 30000000,
        ],
    ];

    protected array $multipliers = [
        'minor' => 1.0,
        'moderate' => 1.0,
        'severe' => 1.0,
        'critical' => 1.0,
    ];

    public function __construct()
    {
        $customMatrix = SystemSetting::get('damage_cost_matrix');
        if ($customMatrix && is_array($customMatrix)) {
            $this->costMatrix = array_merge($this->costMatrix, $customMatrix);
        }

        $customMultipliers = SystemSetting::get('damage_severity_multipliers');
        if ($customMultipliers && is_array($customMultipliers)) {
            $this->multipliers = array_merge($this->multipliers, $customMultipliers);
        }
    }

    public function calculateCost(string $damageType, string $severity): float
    {
        $baseCost = $this->costMatrix[$damageType][$severity]
            ?? $this->costMatrix['scratch']['minor'];

        $multiplier = $this->multipliers[$severity] ?? 1.0;

        return round($baseCost * $multiplier, 2);
    }

    public function assessDamage(DamageReport $report, float $actualCost, int $userId): DamageReport
    {
        $report->update([
            'actual_cost' => $actualCost,
            'assessed_by' => $userId,
            'assessed_at' => now(),
            'status' => 'assessed',
        ]);

        return $report->fresh();
    }

    public function estimateBatch(array $damages): float
    {
        $total = 0.0;

        foreach ($damages as $damage) {
            $total += $this->calculateCost(
                $damage['damage_type'],
                $damage['severity']
            );
        }

        return round($total, 2);
    }

    public function getCostMatrix(): array
    {
        return $this->costMatrix;
    }

    public function getSeverityOptions(): array
    {
        return array_keys($this->multipliers);
    }

    public function getDamageTypeOptions(): array
    {
        return array_keys($this->costMatrix);
    }
}
