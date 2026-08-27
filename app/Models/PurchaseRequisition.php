<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseRequisition extends Model
{
    use BelongsToLocation, SoftDeletes;

    protected $fillable = ['requisition_number', 'location_id', 'warehouse_id', 'requested_by', 'department', 'request_date', 'required_date', 'priority', 'status', 'estimated_total', 'notes', 'approved_by', 'submitted_at', 'approved_at', 'rejected_at', 'decision_notes'];

    protected function casts(): array
    {
        return ['request_date' => 'date', 'required_date' => 'date', 'estimated_total' => 'decimal:2', 'submitted_at' => 'datetime', 'approved_at' => 'datetime', 'rejected_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->requisition_number ??= 'PR-'.now()->format('ymd').'-'.Str::upper(Str::random(6)));
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(SparePartPurchaseOrder::class);
    }
}
