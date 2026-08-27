<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'part_number',
        'category_id',
        'unit_price',
        'stock',
        'min_stock',
        'location_in_store',
        'supplier_name',
        'supplier_phone',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
