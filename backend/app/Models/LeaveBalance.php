<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 'leave_balances';

    protected $primaryKey = 'balance_id';

    protected $fillable = [
        'employee_id',
        'vacation_earned',
        'sick_earned',
        'vacation_balance',
        'sick_balance',
        'used_leave',
        'last_updated'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id');
    }
}