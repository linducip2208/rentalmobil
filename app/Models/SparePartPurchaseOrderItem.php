<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartPurchaseOrderItem extends Model
{
    protected $fillable = [
        'spare_part_purchase_order_id',
        'spare_part_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'received_quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchaseOrder::class, 'spare_part_purchase_order_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
