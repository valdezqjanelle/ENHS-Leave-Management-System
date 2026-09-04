<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingPersonnelRecord extends Model
{
    protected $table = 'teaching_personnel_records';

    protected $primaryKey = 'teaching_record_id';

    protected $fillable = [
        'employee_id',
        'subject_specialization',
        'grade_level_handled',
        'advisory_class',
        'teaching_load',
        'teaching_hours',
    ];

    protected $casts = [
        'teaching_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeRecord::class,
            'employee_id',
            'employee_id'
        );
    }

    public function assignments()
    {
        return $this->hasMany(
            TeachingAssignment::class,
            'teaching_record_id',
            'teaching_record_id'
        );
    }
}
