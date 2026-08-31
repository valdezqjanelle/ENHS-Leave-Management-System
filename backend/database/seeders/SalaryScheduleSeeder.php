<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $salarySchedules = [

        [ 'salary_grade' => 1, 'steps' => [
                14634,
                  14730,
                14,849,
                  14968,
                  15089,
                  15211,
                  15333,
                  15456,
        ], ],
         [ 'salary_grade' => 2, 'steps' => [


         ], ],

            // Salary Grade 8
            [
                'salary_grade' => 8,
                'steps' => [
                    22423,
                    22627,
                    22832,
                    23038,
                    23246,
                    23456,
                    23668,
                    23883,
                ],
            ],

            // Salary Grade 9
            [
                'salary_grade' => 9,
                'steps' => [
                    24329,
                    24523,
                    24720,
                    24917,
                    25117,
                    25318,
                    25521,
                    25725,
                ],
            ],

            // Salary Grade 11 - Teacher I
            [
                'salary_grade' => 11,
                'steps' => [
                    31705,
                    31820,
                    32109,
                    32401,
                    32697,
                    32998,
                    33302,
                    33611,
                ],
            ],

            // Salary Grade 12 - Teacher II
            [
                'salary_grade' => 12,
                'steps' => [
                    33947,
                    34069,
                    34357,
                    34648,
                    34943,
                    35242,
                    35544,
                    35850,
                ],
            ],

            // Salary Grade 13 - Teacher III / Special Science Teacher I
            [
                'salary_grade' => 13,
                'steps' => [
                    36125,
                    36283,
                    36599,
                    36919,
                    37244,
                    37572,
                    37904,
                    38241,
                ],
            ],

            // Salary Grade 18 - Master Teacher I
            [
                'salary_grade' => 18,
                'steps' => [
                    53818,
                    54371,
                    54933,
                    55499,
                    56075,
                    56657,
                    57246,
                    57842,
                ],
            ],

            // Salary Grade 19 - Master Teacher II / School Principal I
            [
                'salary_grade' => 19,
                'steps' => [
                    59153,
                    59966,
                    60793,
                    61632,
                    62486,
                    63353,
                    64236,
                    65132,
                ],
            ],

            // Salary Grade 20 - Master Teacher III
            [
                'salary_grade' => 20,
                'steps' => [
                    66052,
                    66970,
                    67904,
                    68853,
                    69818,
                    70772,
                    71727,
                    72671,
                ],
            ],
        ];

        foreach ($salarySchedules as $schedule) {

            foreach ($schedule['steps'] as $index => $salary) {

                DB::table('salary_schedules')->updateOrInsert(
                    [
                        'salary_grade' => $schedule['salary_grade'],
                        'step' => $index + 1,
                    ],
                    [
                        'salary' => $salary,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}