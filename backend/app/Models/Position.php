<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['code', 'name', 'type', 'salary_grade'];

    public function employeeRecords()
    {
        return $this->hasMany(EmployeeRecord::class);
    }
}