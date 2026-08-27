<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInvestment extends Model
{
    protected $fillable = [
        'investor_account_id',
        'vehicle_id',
        'share_percent',
        'invested_amount',
        'started_at',
        'ended_at',
        'status',
    ];


    protected function casts(): array
    {
        return [
            'share_percent' => 'decimal:2',
            'invested_amount' => 'decimal:2',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function investorAccount(): BelongsTo { return $this->belongsTo(InvestorAccount::class); }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }

    public function distributions(): HasMany { return $this->hasMany(InvestorDistribution::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}
