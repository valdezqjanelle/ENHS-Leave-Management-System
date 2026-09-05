<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'department_name' => 'Filipino',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'English',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'Mathematics',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'Science',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'MAPEH',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'Araling Panlipunan',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'ESP / Values Education',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'TLE',
                'level' => 'JHS',
            ],
            [
                'department_name' => 'STEM',
                'level' => 'SHS',
            ],
            [
                'department_name' => 'HUMSS',
                'level' => 'SHS',
            ],
            [
                'department_name' => 'ABM',
                'level' => 'SHS',
            ],
            [
                'department_name' => 'TVL - ICT',
                'level' => 'SHS',
            ],
            [
                'department_name' => 'School Administration',
                'level' => 'Non-Teaching',
            ],
            [
                'department_name' => 'Administrative Services',
                'level' => 'Non-Teaching',
            ],
            [
                'department_name' => 'Finance and Disbursement',
                'level' => 'Non-Teaching',
            ],
            [
                'department_name' => 'General Services',
                'level' => 'Non-Teaching',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                [
                    'department_name' =>
                        $department['department_name'],

                    'level' =>
                        $department['level'],
                ],
                $department
            );
        }
    }
}