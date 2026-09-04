<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSection extends Model
{
    protected $primaryKey = 'section_id';
    protected $fillable = ['grade_level_id', 'section_name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function gradeLevel() { return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'grade_level_id'); }
    public function assignments() { return $this->hasMany(TeachingAssignment::class, 'section_id', 'section_id'); }
}
