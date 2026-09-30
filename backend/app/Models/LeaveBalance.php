<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use SoftDeletes;

    protected $table = 'leave_balances';

    protected $primaryKey = 'balance_id';

    protected $fillable = [
        'employee_id',
        'vacation_earned',
        'sick_earned',
        'vacation_balance',
        'sick_balance',
        'service_credits',
        'used_leave',
        'last_updated'
    ];

    protected $casts = [
        'vacation_earned' => 'decimal:3',
        'sick_earned' => 'decimal:3',
        'vacation_balance' => 'decimal:3',
        'sick_balance' => 'decimal:3',
        'service_credits' => 'decimal:3',
        'used_leave' => 'decimal:3',
        'last_updated' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id');
    }
}