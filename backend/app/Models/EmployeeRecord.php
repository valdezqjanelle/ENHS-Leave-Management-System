<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveBalance;

class EmployeeRecord extends Model
{
    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'department',
        'position',
        'employee_category',
        'salary',
        'contact_number',
        'employment_status',
        'date_hired'
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function leaveApplications()
{
    return $this->hasMany(
        LeaveApplication::class,
        'employee_id',
        'employee_id'
    );
}

public function leaveBalance()
{
    return $this->hasOne(
        LeaveBalance::class,
        'employee_id'
    );
}
}