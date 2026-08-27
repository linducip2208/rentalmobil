<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = ['movement_number', 'warehouse_id', 'spare_part_id', 'type', 'quantity', 'unit_cost', 'total_cost', 'reference_type', 'reference_id', 'performed_by', 'occurred_at', 'note'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2', 'occurred_at' => 'datetime'];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
