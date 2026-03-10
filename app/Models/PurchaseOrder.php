<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'vendor_id', 'purchase_request_id', 'number', 'status', 'date',
        'expected_delivery', 'notes', 'subtotal', 'tax_amount', 'total_amount', 'created_by',
    ];

    protected $casts = [
        'date'              => 'date',
        'expected_delivery' => 'date',
        'subtotal'          => 'decimal:2',
        'tax_amount'        => 'decimal:2',
        'total_amount'      => 'decimal:2',
    ];

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
    public function vendorBills(): HasMany { return $this->hasMany(VendorBill::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->number = $m->number ?? static::generateNumber());
    }

    public static function generateNumber(): string
    {
        $last = static::latest()->first();
        $next = $last ? (intval(substr($last->number, -4)) + 1) : 1;
        return 'PO-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
