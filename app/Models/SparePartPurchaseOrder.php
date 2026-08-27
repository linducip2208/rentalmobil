<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePartPurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_name',
        'supplier_phone',
        'status',
        'expected_at',
        'total_amount',
        'created_by',
        'received_at',
        'notes',
    ];


    protected function casts(): array
    {
        return [
            'expected_at' => 'date',
            'total_amount' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    protected static function boot(): void { parent::boot(); static::creating(function (self $po) { if (blank($po->po_number)) { $po->po_number = 'PO-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)); } }); }

    public function items(): HasMany { return $this->hasMany(SparePartPurchaseOrderItem::class); }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function receiveAll(): void { foreach ($this->items as $item) { $part = $item->sparePart; $part->increment('stock', $item->quantity); $item->update(['received_quantity' => $item->quantity]); } $this->update(['status' => 'received', 'received_at' => now()]); }
}
