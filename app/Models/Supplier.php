<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'contact_person', 'tax_number', 'email', 'phone', 'address', 'payment_terms_days', 'credit_limit', 'bank_details', 'rating', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['payment_terms_days' => 'integer', 'credit_limit' => 'decimal:2', 'bank_details' => 'array', 'rating' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(SparePartPurchaseOrder::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }
}
