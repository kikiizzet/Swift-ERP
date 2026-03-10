<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'customer_id', 'number', 'status', 'date', 'expiry_date',
        'notes', 'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->number = $model->number ?? static::generateNumber();
        });
    }

    public static function generateNumber(): string
    {
        $last = static::latest()->first();
        $next = $last ? (intval(substr($last->number, -4)) + 1) : 1;
        return 'SO-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
