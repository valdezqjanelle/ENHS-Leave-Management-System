<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    protected $primaryKey = 'assignment_id';
    protected $fillable = ['teaching_record_id', 'grade_level_id', 'section_id', 'subject_id', 'school_year', 'is_advisory', 'teaching_hours'];
    protected $casts = ['is_advisory' => 'boolean', 'teaching_hours' => 'decimal:2'];
    public function gradeLevel() { return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'grade_level_id'); }
    public function section() { return $this->belongsTo(SchoolSection::class, 'section_id', 'section_id'); }
    public function subject() { return $this->belongsTo(TeachingSubject::class, 'subject_id', 'subject_id'); }
}
