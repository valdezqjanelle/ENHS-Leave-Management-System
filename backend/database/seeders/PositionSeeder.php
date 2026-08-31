<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['code' => 'TCH',   'name' => 'Teacher',                        'type' => 'Teaching',     'salary_grade' => '11'],
            ['code' => 'TCH1',  'name' => 'Teacher I',                      'type' => 'Teaching',     'salary_grade' => '11'],
            ['code' => 'TCH2',  'name' => 'Teacher II',                     'type' => 'Teaching',     'salary_grade' => '12'],
            ['code' => 'TCH3',  'name' => 'Teacher III',                    'type' => 'Teaching',     'salary_grade' => '13'],
            ['code' => 'MT1',   'name' => 'Master Teacher I',               'type' => 'Teaching',     'salary_grade' => '18'],
            ['code' => 'MT2',   'name' => 'Master Teacher II',              'type' => 'Teaching',     'salary_grade' => '19'],
            ['code' => 'MT3',   'name' => 'Master Teacher III',             'type' => 'Teaching',     'salary_grade' => '20'],
            ['code' => 'SST1',  'name' => 'Special Science Teacher I',      'type' => 'Teaching',     'salary_grade' => '13'],
            ['code' => 'LSBT',  'name' => 'LSB Teacher',                    'type' => 'Teaching',     'salary_grade' => null],
            ['code' => 'PSBT',  'name' => 'PSB Teacher',                    'type' => 'Teaching',     'salary_grade' => null],
            ['code' => 'PRIN1', 'name' => 'School Principal I',             'type' => 'School Head',  'salary_grade' => '19'],
            ['code' => 'ADAS2', 'name' => 'Administrative Assistant II',    'type' => 'Non-Teaching', 'salary_grade' => '8'],
            ['code' => 'ADAS3', 'name' => 'Administrative Assistant III',   'type' => 'Non-Teaching', 'salary_grade' => '9'],
            ['code' => 'UTIL1',  'name' => 'Utility Worker I',              'type' => 'Non-Teaching', 'salary_grade' => '1'],

        ];

        foreach ($positions as $position) {
            DB::table('positions')->updateOrInsert(
                ['code' => $position['code']],
                array_merge($position, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}