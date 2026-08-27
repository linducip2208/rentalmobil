<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class StockTransfer extends Model
{
    protected $fillable = ['transfer_number', 'source_warehouse_id', 'destination_warehouse_id', 'status', 'transfer_date', 'requested_by', 'approved_by', 'received_by', 'shipped_at', 'received_at', 'notes'];

    protected function casts(): array
    {
        return ['transfer_date' => 'date', 'shipped_at' => 'datetime', 'received_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->transfer_number ??= 'ST-'.now()->format('ymd').'-'.Str::upper(Str::random(6)));
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
