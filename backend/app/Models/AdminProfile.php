<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'extension_name',
        'date_of_birth',
        'sex',
        'civil_status',
        'nationality',
        'address',
        'personal_email',
        'level',
        'position_id',
        'salary_step',
        'salary',
        'department_id',
        'contact_number',
        'emergency_contact_name',
        'emergency_contact_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'position_id' => 'integer',
        'salary_step' => 'integer',
        'salary' => 'decimal:2',
        'department_id' => 'integer',
    ];

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

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }
}