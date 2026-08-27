<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionItem extends Model
{
    protected $fillable = ['purchase_requisition_id', 'spare_part_id', 'quantity', 'estimated_unit_price', 'estimated_total', 'notes'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'estimated_unit_price' => 'decimal:2', 'estimated_total' => 'decimal:2'];
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
