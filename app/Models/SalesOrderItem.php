<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StockMovement;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'product_id', 'variant_id', 'product_name',
        'quantity', 'unit', 'unit_price', 'discount_percent', 'tax_percent', 'subtotal',
    ];

    protected $casts = [
        'quantity'         => 'decimal:4',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::saving(function ($item) {
            $base      = (float) $item->quantity * (float) $item->unit_price;
            $discount  = $base * ((float) ($item->discount_percent ?? 0) / 100);
            $afterDisc = $base - $discount;
            $item->subtotal = round($afterDisc + ($afterDisc * ((float) ($item->tax_percent ?? 0) / 100)), 2);
        });

        static::created(function ($item) {
            if ($item->product && $item->product->type === 'storable') {
                StockMovement::record(
                    $item->product,
                    'out',
                    $item->quantity,
                    'Penjualan (Sales Order #' . ($item->salesOrder->number ?? '-') . ')',
                    'sales_order',
                    $item->sales_order_id
                );
            }
        });
    }

    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
