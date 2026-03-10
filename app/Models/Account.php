<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = ['code', 'name', 'type', 'parent_id', 'is_active', 'notes'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent(): BelongsTo { return $this->belongsTo(Account::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Account::class, 'parent_id'); }
    public function journalEntryLines(): HasMany { return $this->hasMany(JournalEntryLine::class); }

    /**
     * Hitung saldo akun.
     */
    public function getBalanceAttribute(): float
    {
        $debit = $this->journalEntryLines()->sum('debit');
        $credit = $this->journalEntryLines()->sum('credit');

        return match($this->type) {
            'asset', 'expense' => $debit - $credit,
            default            => $credit - $debit,
        };
    }
}
