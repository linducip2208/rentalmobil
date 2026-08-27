<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDistribution extends Model
{
    protected $fillable = [
        'vehicle_investment_id',
        'period_month',
        'revenue_share',
        'expense_share',
        'depreciation_share',
        'net_payout',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'revenue_share' => 'decimal:2',
            'expense_share' => 'decimal:2',
            'depreciation_share' => 'decimal:2',
            'net_payout' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(VehicleInvestment::class, 'vehicle_investment_id');
    }

    public function markPaid(): void
    {
        $this->update(['status' => 'paid', 'paid_at' => now()]);
    }
}
