<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 'description', 'type',
        'sales_price', 'cost_price', 'stock_quantity', 'min_stock_quantity',
        'unit', 'is_active',
    ];

    protected $casts = [
        'sales_price'        => 'decimal:2',
        'cost_price'         => 'decimal:2',
        'stock_quantity'     => 'integer',
        'min_stock_quantity' => 'integer',
        'is_active'          => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_quantity;
    }
}
