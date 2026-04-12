<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'variant_id', 'type', 'reference_type', 'reference_id',
        'quantity', 'quantity_before', 'quantity_after', 'unit_cost',
        'warehouse', 'warehouse_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'        => 'decimal:4',
        'quantity_before' => 'decimal:4',
        'quantity_after'  => 'decimal:4',
        'unit_cost'       => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function warehouse_rel(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Catat pergerakan stok dan update kuantitas produk secara otomatis.
     */
    public static function record(
        Product $product,
        string $type,
        float $quantity,
        ?string $notes = null,
        ?string $refType = null,
        ?int $refId = null,
        string $warehouse = 'Gudang Utama',
        ?int $warehouseId = null,
    ): self {
        $before = (float) $product->stock_quantity;
        $after = $type === 'in' ? $before + $quantity : $before - abs($quantity);

        $movement = static::create([
            'product_id'       => $product->id,
            'type'             => $type,
            'quantity'         => abs($quantity),
            'quantity_before'  => $before,
            'quantity_after'   => $after,
            'reference_type'   => $refType,
            'reference_id'     => $refId,
            'warehouse'        => $warehouse,
            'warehouse_id'     => $warehouseId,
            'notes'            => $notes,
            'created_by'       => auth()->id(),
        ]);

        $product->update(['stock_quantity' => $after]);

        return $movement;
    }
}
