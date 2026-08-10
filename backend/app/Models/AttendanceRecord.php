<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'time_in',
        'time_out',
        'status',
        'remarks'
    ];

    public function employee()
    {
        return $this->belongsTo(
            EmployeeRecord::class,
            'employee_id'
        );
    }
}