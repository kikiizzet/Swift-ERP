<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StockMovement;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'product_name',
        'quantity', 'received_quantity', 'unit', 'unit_price', 'tax_percent', 'subtotal',
    ];

    protected $casts = [
        'quantity'          => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'unit_price'        => 'decimal:2',
        'tax_percent'       => 'decimal:2',
        'subtotal'          => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($item) {
            $base = (float) $item->quantity * (float) $item->unit_price;
            $item->subtotal = round($base + ($base * ((float) ($item->tax_percent ?? 0) / 100)), 2);
        });

        static::created(function ($item) {
            if ($item->product && $item->product->type === 'storable') {
                StockMovement::record(
                    $item->product,
                    'in',
                    $item->quantity,
                    'Pembelian (Purchase Order #' . ($item->purchaseOrder->number ?? '-') . ')',
                    'purchase_order',
                    $item->purchase_order_id
                );
            }
        });
    }

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
