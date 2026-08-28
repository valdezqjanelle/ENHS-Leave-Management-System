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

    protected $dates = ['deleted_at'];

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id');
    }
}