<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';
    protected $primaryKey = 'leave_id';

    protected $fillable = [

        // RELATIONSHIPS
        'employee_id',
        'leave_type_id',

        // BASIC INFO
        'date_filed',

        // VACATION
        'vacation_location_type',
        'vacation_location',

        // SICK
        'sick_type',
        'illness',

        // STUDY
        'masters_degree',
        'board_exam_review',

        // OTHER PURPOSES
        'monetization',
        'terminal_leave',
        'other_purpose',

        // DATES
        'number_of_days',
        'start_date',
        'end_date',

        // COMMUTATION
        'commutation',

        // REASON
        'reason',
        'applicants_signature',

        // CERTIFICATION
        'certification_as_of',

        'vacation_total_earned',
        'vacation_less_application',
        'vacation_balance',

        'sick_total_earned',
        'sick_less_application',
        'sick_balance',

        // RECOMMENDATION
        'recommendation_status',
        'recommendation_reason',

        // APPROVAL
        'days_with_pay',
        'days_without_pay',
        'other_approval',

        // FINAL STATUS
        'final_status',
        'disapproval_reason',
        'admin_remarks',
    ];

    protected $casts = [
        'date_filed' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'certification_as_of' => 'date',

        'masters_degree' => 'boolean',
        'board_exam_review' => 'boolean',
        'monetization' => 'boolean',
        'terminal_leave' => 'boolean',

        'vacation_total_earned' => 'decimal:2',
        'vacation_less_application' => 'decimal:2',
        'vacation_balance' => 'decimal:2',

        'sick_total_earned' => 'decimal:2',
        'sick_less_application' => 'decimal:2',
        'sick_balance' => 'decimal:2',
    ];

    public $timestamps = true;

    public function employee()
    {
        return $this->belongsTo(EmployeeRecord::class, 'employee_id', 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class, 'leave_id', 'leave_id');
    }
}