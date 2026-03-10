<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'sku', 'extra_price', 'stock_quantity', 'attributes'];

    protected $casts = [
        'extra_price'    => 'decimal:2',
        'stock_quantity' => 'integer',
        'attributes'     => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
