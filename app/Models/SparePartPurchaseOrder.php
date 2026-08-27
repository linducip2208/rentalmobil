<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SparePartPurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id', 'location_id', 'warehouse_id',
        'supplier_name',
        'supplier_phone',
        'status', 'order_date', 'subtotal', 'tax_amount', 'discount_amount',
        'expected_at',
        'total_amount',
        'created_by', 'approved_by', 'approved_at',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_at' => 'date',
            'order_date' => 'date',
            'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'received_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $po) {
            if (blank($po->po_number)) {
                $po->po_number = 'PO-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(SparePartPurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
