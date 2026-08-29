<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonTeachingPersonnelRecord extends Model
{
    protected $table = 'non_teaching_personnel_records';

    protected $primaryKey = 'non_teaching_record_id';

    protected $fillable = [
        'employee_id',
        'office_assignment',
        'job_assignment',
        'job_description',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeRecord::class,
            'employee_id',
            'employee_id'
        );
    }
}