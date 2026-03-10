<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'employee_number', 'name', 'email', 'phone',
        'department_id', 'job_position_id', 'manager_id',
        'join_date', 'birth_date', 'gender', 'marital_status', 'address',
        'id_number', 'tax_number', 'bank_name', 'bank_account_number', 'status',
    ];

    protected $casts = [
        'join_date'  => 'date',
        'birth_date' => 'date',
    ];

    public function user(): BelongsTo        { return $this->belongsTo(User::class); }
    public function department(): BelongsTo  { return $this->belongsTo(Department::class); }
    public function jobPosition(): BelongsTo { return $this->belongsTo(JobPosition::class); }
    public function manager(): BelongsTo     { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function attendances(): HasMany   { return $this->hasMany(Attendance::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function payrolls(): HasMany      { return $this->hasMany(Payroll::class); }
}
