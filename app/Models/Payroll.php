<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'period', 'basic_salary', 'total_allowances',
        'total_deductions', 'net_salary', 'status', 'components', 'pay_date', 'created_by',
    ];

    //cast format
    protected $casts = [
        'basic_salary'      => 'decimal:2',
        'total_allowances'  => 'decimal:2',
        'total_deductions'  => 'decimal:2',
        'net_salary'        => 'decimal:2',
        'components'        => 'array',
        'pay_date'          => 'date',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * Hitung gaji bersih otomatis.
     */
    public function calculateNetSalary(): static
    {
        $this->net_salary = $this->basic_salary + $this->total_allowances - $this->total_deductions;
        return $this;
    }
}
