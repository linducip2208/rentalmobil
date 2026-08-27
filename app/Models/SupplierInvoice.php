<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SupplierInvoice extends Model
{
    use BelongsToLocation, SoftDeletes;

    protected $fillable = ['bill_number', 'supplier_invoice_number', 'supplier_id', 'location_id', 'spare_part_purchase_order_id', 'goods_receipt_id', 'invoice_date', 'due_date', 'subtotal', 'tax_amount', 'discount_amount', 'total', 'paid_amount', 'status', 'attachments', 'notes', 'created_by', 'posted_by', 'posted_at'];

    protected function casts(): array
    {
        return ['invoice_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'attachments' => 'array', 'posted_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->bill_number ??= 'BILL-'.now()->format('ymd').'-'.Str::upper(Str::random(6)));
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchaseOrder::class, 'spare_part_purchase_order_id');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getOutstandingAmountAttribute(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    public function getDaysOverdueAttribute(): int
    {
        return $this->outstanding_amount > 0 && $this->due_date->isPast() ? $this->due_date->diffInDays(now()) : 0;
    }

    public function getAgingBucketAttribute(): string
    {
        return match (true) {
            $this->days_overdue === 0 => 'current',$this->days_overdue <= 30 => '1-30',$this->days_overdue <= 60 => '31-60',$this->days_overdue <= 90 => '61-90',default => '>90'
        };
    }
}
