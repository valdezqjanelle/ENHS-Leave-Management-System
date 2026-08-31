<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';

    protected $primaryKey = 'leave_type_id';

    protected $fillable = [
        'code',
        'leave_type_name',
        'legal_basis',
    ];

    protected $casts = [
        'leave_type_id' => 'integer',
    ];

    public function leaveApplications()
    {
        return $this->hasMany(
            LeaveApplication::class,
            'leave_type_id',
            'leave_type_id'
        );
    }
}