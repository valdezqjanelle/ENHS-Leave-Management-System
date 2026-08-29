<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'extension_name',
        'date_of_birth',
        'sex',
        'civil_status',
        'nationality',
        'address',
        'contact_number',
        'personal_email',
        'emergency_contact_name',
        'emergency_contact_number',
        'personnel_type',
        'employment_status',
        'employment_category',
        'date_hired',
        'department_id',
        'position_id',
        'supervisor_id',
        'salary_step',
        'salary',
    ];

    protected $appends = [
        'department_name',
        'years_of_service',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'date_of_birth' => 'date',
        'salary' => 'decimal:2',
    ];

    public function getDepartmentNameAttribute()
    {
        return $this->department?->department_name;
    }

 public function getYearsOfServiceAttribute()
{
    if (!$this->date_hired) {
        return null;
    }

    return (int) Carbon::parse($this->date_hired)->diffInYears(now());
}

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'user_id'
        );
    }

    public function position()
    {
        return $this->belongsTo(
            Position::class,
            'position_id',
            'id'
        );
    }

    public function department()
    {
        return $this->belongsTo(
            Department::class,
            'department_id',
            'department_id'
        );
    }

    public function supervisor()
    {
        return $this->belongsTo(
            EmployeeRecord::class,
            'supervisor_id',
            'employee_id'
        );
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