<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    protected $fillable = ['warehouse_id', 'spare_part_id', 'on_hand', 'reserved', 'average_cost', 'minimum_stock', 'reorder_level', 'reorder_quantity'];

    protected function casts(): array
    {
        return ['on_hand' => 'decimal:3', 'reserved' => 'decimal:3', 'average_cost' => 'decimal:2', 'minimum_stock' => 'decimal:3', 'reorder_level' => 'decimal:3', 'reorder_quantity' => 'decimal:3'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function getAvailableAttribute(): float
    {
        return round((float) $this->on_hand - (float) $this->reserved, 3);
    }
}
