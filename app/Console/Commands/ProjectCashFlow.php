<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectCashFlow extends Command
{
    protected $signature = 'finance:project-cashflow {--days=90}';

    protected $description = 'Simpan snapshot proyeksi arus kas (inflow/outflow/net)';

    public function handle(): int
    {
$snapshot = app(\App\Services\CashFlowProjectionService::class)->project((int) $this->option('days'));

$this->table(
    ['Tanggal', 'Horizon', 'Inflow', 'Outflow', 'Net'],
    [[
        $snapshot->as_of_date->format('d/m/Y'),
        $snapshot->horizon_days . ' hari',
        'Rp' . number_format((float) $snapshot->projected_inflow, 0, ',', '.'),
        'Rp' . number_format((float) $snapshot->projected_outflow, 0, ',', '.'),
        'Rp' . number_format((float) $snapshot->net_projection, 0, ',', '.'),
    ]]
);

return self::SUCCESS;
    }
}
