<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkTimeConversionSeeder extends Seeder
{
    public function run(): void
    {
        // Exact values in the supplied image, including 30 = .062 and 54 = .112.
        $minutes = [
            '0.002','0.004','0.006','0.008','0.010','0.012','0.015','0.017','0.019','0.021',
            '0.023','0.025','0.027','0.029','0.031','0.033','0.035','0.037','0.040','0.042',
            '0.044','0.046','0.048','0.050','0.052','0.054','0.056','0.058','0.060','0.062',
            '0.065','0.067','0.069','0.071','0.073','0.075','0.077','0.079','0.081','0.083',
            '0.085','0.087','0.090','0.092','0.094','0.096','0.098','0.100','0.102','0.104',
            '0.106','0.108','0.110','0.112','0.115','0.117','0.119','0.121','0.123','0.125',
        ];
        $rows = [];
        for ($hour = 1; $hour <= 8; $hour++) {
            $rows[] = ['unit' => 'hour', 'quantity' => $hour,
                'equivalent_days' => number_format($hour / 8, 3, '.', '')];
        }
        foreach ($minutes as $index => $days) {
            $rows[] = ['unit' => 'minute', 'quantity' => $index + 1,
                'equivalent_days' => $days];
        }
        // Safe to rerun: does not create duplicate reference rows.
        DB::table('work_time_conversions')->upsert(
            $rows, ['unit', 'quantity'], ['equivalent_days']
        );
    }
}
