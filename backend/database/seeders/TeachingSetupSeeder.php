<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\TeachingSubject;
use Illuminate\Database\Seeder;

class TeachingSetupSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Grade 7','JHS',7],['Grade 8','JHS',8],['Grade 9','JHS',9],['Grade 10','JHS',10],['Grade 11','SHS',11],['Grade 12','SHS',12]] as [$name,$level,$order]) {
            GradeLevel::updateOrCreate(['grade_name' => $name, 'level' => $level], ['sort_order' => $order, 'is_active' => true]);
        }

        Department::whereIn('level', ['JHS', 'SHS'])->each(function ($department) {
            TeachingSubject::updateOrCreate(
                ['department_id' => $department->department_id, 'subject_name' => $department->department_name, 'level' => $department->level],
                ['is_active' => true]
            );
        });
    }
}
