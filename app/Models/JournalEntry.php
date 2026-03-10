<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_id', 'number', 'date', 'reference', 'description',
        'status', 'reference_type', 'reference_id', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function journal(): BelongsTo { return $this->belongsTo(Journal::class); }
    public function lines(): HasMany { return $this->hasMany(JournalEntryLine::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * Pastikan entri debit = kredit (balanced entry).
     */
    public function isBalanced(): bool
    {
        return $this->lines->sum('debit') == $this->lines->sum('credit');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->number = $m->number ?? 'JE-'.date('Y').'-'.str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT));
    }
}
