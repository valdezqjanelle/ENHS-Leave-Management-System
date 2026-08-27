<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveBalance;
use App\Models\User;

class EmployeeRecord extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'user_id',
        'created_by',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'department',
        'position_id',
        'employee_category',
        'salary',
        'contact_number',
        'employment_status',
        'date_hired',
        'salary_step'
    ];

    protected $dates = ['deleted_at'];
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function createdBy()
    {
    return $this->belongsTo(User::class, 'created_by', 'user_id');
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