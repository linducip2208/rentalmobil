<?php

namespace App\Services;

use App\Models\CompetitorPrice;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

/**
 * Intel harga kompetitor: input manual / import CSV, lalu
 * rekomendasi rate harian per kategori berdasarkan persentil pasar.
 */
class CompetitorPriceService
{
    /**
     * Rekomendasi rate harian untuk kendaraan berdasarkan median kompetitor 30 hari.
     * Strategi: match_median | undercut_5 | premium_10 (SystemSetting "competitor_strategy").
     */
    public function suggestDailyRate(Vehicle $vehicle): ?array
    {
        $prices = $this->recentPrices($vehicle->category_id);

        if ($prices->isEmpty()) {
            return null;
        }

        $median = $this->percentile($prices, 50);
        $p25 = $this->percentile($prices, 25);
        $p75 = $this->percentile($prices, 75);

        $strategy = SystemSetting::get('competitor_strategy', 'match_median');

        $suggested = match ($strategy) {
            'undercut_5' => round($median * 0.95, -3),
            'premium_10' => round($median * 1.10, -3),
            default => round($median, -3),
        };

        return [
            'suggested_daily_rate' => $suggested,
            'current_daily_rate' => (float) $vehicle->daily_rate,
            'market_median' => round($median, 2),
            'market_p25' => round($p25, 2),
            'market_p75' => round($p75, 2),
            'position_vs_market_pct' => $median > 0 ? round(((float) $vehicle->daily_rate - $median) / $median * 100, 1) : null,
            'sample_size' => $prices->count(),
            'strategy' => $strategy,
        ];
    }

    public function categorySummary(?int $categoryId = null): Collection
    {
        $query = CompetitorPrice::query()->recent(60)->selectRaw('category_id, AVG(daily_rate) as avg_rate, MIN(daily_rate) as min_rate, MAX(daily_rate) as max_rate, COUNT(*) as samples')->groupBy('category_id');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }

    /**
     * Import CSV dengan header: competitor_name,city,daily_rate,observed_at[,category_name]
     */
    public function importCsv(string $csvContent, ?int $defaultCategoryId = null): array
    {
        $rows = array_filter(array_map('trim', explode("\n", $csvContent)));
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0 && str_contains(strtolower($row), 'competitor')) {
                continue;
            }

            $cols = str_getcsv($row);

            if (count($cols) < 4 || ! is_numeric(str_replace([',', '.'], ['', '.'], $cols[2] ?? ''))) {
                $errors[] = ['line' => $index + 1, 'reason' => 'Format tidak valid'];

                continue;
            }

            CompetitorPrice::create([
                'competitor_name' => trim($cols[0]),
                'city' => trim($cols[1]),
                'daily_rate' => (float) str_replace(',', '', $cols[2]),
                'observed_at' => trim($cols[3]) ?: now()->toDateString(),
                'category_id' => $defaultCategoryId,
            ]);

            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    protected function recentPrices(int $categoryId): Collection
    {
        return CompetitorPrice::where('category_id', $categoryId)
            ->recent(30)
            ->pluck('daily_rate')
            ->map(fn ($rate) => (float) $rate)
            ->values();
    }

    protected function percentile(Collection $values, int $pct): float
    {
        if ($values->isEmpty()) {
            return 0.0;
        }

        $sorted = $values->sort()->values();
        $index = ($sorted->count() - 1) * $pct / 100;
        $floor = (int) floor($index);
        $ceil = (int) ceil($index);

        if ($floor === $ceil) {
            return (float) $sorted->get($floor);
        }

        return (float) ($sorted->get($floor) * ($ceil - $index) + $sorted->get($ceil) * ($index - $floor));
    }
}
