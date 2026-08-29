<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // JHS
            ['department_name' => 'Filipino', 'level' => 'JHS'],
            ['department_name' => 'English', 'level' => 'JHS'],
            ['department_name' => 'Mathematics', 'level' => 'JHS'],
            ['department_name' => 'Science', 'level' => 'JHS'],
            ['department_name' => 'MAPEH', 'level' => 'JHS'],
            ['department_name' => 'Araling Panlipunan', 'level' => 'JHS'],
            ['department_name' => 'ESP / Values Education', 'level' => 'JHS'],
            ['department_name' => 'TLE', 'level' => 'JHS'],

            // SHS
            ['department_name' => 'Biological Science', 'level' => 'SHS'],
            ['department_name' => 'Physical Education', 'level' => 'SHS'],
            ['department_name' => 'Mathematics', 'level' => 'SHS'],
            ['department_name' => 'English', 'level' => 'SHS'],
            ['department_name' => 'Social Science', 'level' => 'SHS'],
            ['department_name' => 'Filipino', 'level' => 'SHS'],
            ['department_name' => 'Araling Panlipunan', 'level' => 'SHS'],
            ['department_name' => 'Commerce', 'level' => 'SHS'],
            ['department_name' => 'Management Accounting', 'level' => 'SHS'],
            ['department_name' => 'ABM', 'level' => 'SHS'],
            ['department_name' => 'ICT', 'level' => 'SHS'],

            // Non-Teaching
            ['department_name' => 'School Administration', 'level' => 'Non-Teaching'],
            ['department_name' => 'Senior Bookeeper', 'level' => 'Non-Teaching'],
            ['department_name' => 'Clerk', 'level' => 'Non-Teaching'],
            ['department_name' => 'Disbursing Officer', 'level' => 'Non-Teaching'],
            ['department_name' => 'Utility / Job Order', 'level' => 'Non-Teaching'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}