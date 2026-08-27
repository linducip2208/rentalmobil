<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GoodsReceipt extends Model
{
    protected $fillable = ['receipt_number', 'spare_part_purchase_order_id', 'warehouse_id', 'received_by', 'supplier_delivery_note', 'status', 'received_at', 'notes'];

    protected function casts(): array
    {
        return ['received_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $receipt) => $receipt->receipt_number ??= 'GR-'.now()->format('ymd').'-'.Str::upper(Str::random(6)));
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchaseOrder::class, 'spare_part_purchase_order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
