<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingSubject extends Model
{
    protected $primaryKey = 'subject_id';
    protected $fillable = ['department_id', 'subject_name', 'level', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function department() { return $this->belongsTo(Department::class, 'department_id', 'department_id'); }
    public function assignments() { return $this->hasMany(TeachingAssignment::class, 'subject_id', 'subject_id'); }
}
