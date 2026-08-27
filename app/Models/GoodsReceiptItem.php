<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    protected $fillable = ['goods_receipt_id', 'spare_part_purchase_order_item_id', 'spare_part_id', 'accepted_quantity', 'rejected_quantity', 'damaged_quantity', 'unit_cost', 'notes'];

    protected function casts(): array
    {
        return ['accepted_quantity' => 'decimal:3', 'rejected_quantity' => 'decimal:3', 'damaged_quantity' => 'decimal:3', 'unit_cost' => 'decimal:2'];
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchaseOrderItem::class, 'spare_part_purchase_order_item_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
