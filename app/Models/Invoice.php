<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'sales_order_id', 'customer_id', 'number', 'status', 'date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount', 'paid_amount', 'notes', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
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
        return 'INV-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
