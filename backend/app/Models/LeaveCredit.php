<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveCredit extends Model
{
    use SoftDeletes;
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
        'recorded_by',
        'posted_bucket',
        'posted_days',
        'revoked_reason',
        'revoked_by',
        'revoked_at',
    ];

    protected $casts = [
        'hours_rendered' => 'decimal:4',
        'equivalent_leave_days' => 'decimal:3',
        'date_recorded' => 'date',
        'posted_days' => 'decimal:3',
        'revoked_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id');
    }
}
