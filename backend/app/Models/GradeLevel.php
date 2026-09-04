<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $primaryKey = 'grade_level_id';
    protected $fillable = ['grade_name', 'level', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];
    public function sections() { return $this->hasMany(SchoolSection::class, 'grade_level_id', 'grade_level_id'); }
    public function assignments() { return $this->hasMany(TeachingAssignment::class, 'grade_level_id', 'grade_level_id'); }
}
