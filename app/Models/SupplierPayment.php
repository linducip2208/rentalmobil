<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupplierPayment extends Model
{
    protected $fillable = ['payment_number', 'supplier_invoice_id', 'supplier_id', 'location_id', 'bank_account_id', 'payment_date', 'amount', 'reference', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'amount' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->payment_number ??= 'SPAY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)));
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
