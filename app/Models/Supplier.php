<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'tax_number', 'email', 'phone', 'address', 'payment_terms_days', 'is_active'];

    protected function casts(): array
    {
        return ['payment_terms_days' => 'integer', 'is_active' => 'boolean'];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(SparePartPurchaseOrder::class);
    }
}
