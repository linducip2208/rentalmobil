<?php

namespace App\Console\Commands;

use App\Services\Gps\DriverScorecardService;
use Illuminate\Console\Command;

class GenerateDriverScorecards extends Command
{
    protected $signature = 'drivers:generate-scorecards {--period=last-month : last-month|this-month}';

    protected $description = 'Generate scorecard driver bulanan (skor perilaku + rating)';

    public function handle(): int
    {
        [$start, $end] = match ($this->option('period')) {
            'this-month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            default => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        };

        $results = app(DriverScorecardService::class)->generate($start, $end);

        $this->info("Scorecard periode {$start} s/d {$end}: {$results['drivers']} driver, {$results['created']} baru.");

        return self::SUCCESS;
    }
}
