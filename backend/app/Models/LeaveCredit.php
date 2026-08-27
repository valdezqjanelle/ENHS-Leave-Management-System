<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCredit extends Model
{
    protected $table = 'leave_credits';

    protected $primaryKey = 'credits_id';

    protected $fillable = [
        'employee_id',
        'activity_name',
        'hours_rendered',
        'equivalent_leave_days',
           'credit_type',
    'status',
        'date_recorded',
        'recorded_by'
    ];

    protected $dates = ['deleted_at'];

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id');
    }
}