<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    DB::table('leave_types')->insert([
        ['leave_type_name' => 'Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules E.O. 292)'],
        ['leave_type_name' => 'Mandatory/Forced Leave (Sec. 51, Rule XVI, Omnibus Rules E.O. 292)'],
        ['leave_type_name' => 'Special Privilege Leave (SPL)'],
        ['leave_type_name' => 'Sick Leave'],
        ['leave_type_name' => 'Maternity Leave'],
        ['leave_type_name' => 'Paternity Leave'],
        ['leave_type_name' => 'Solo Parent Leave'],
        ['leave_type_name' => 'Study Leave'],
        ['leave_type_name' => '10-Day VAWC Leave'],
        ['leave_type_name' => 'Special Leave Benefits for Women (SLBW)'],
        ['leave_type_name'=> 'Rehabilitation Privilege(Sec. 55, Rule XVI, Omnibus Rules E.O. 292)'],
        ['leave_type_name' => 'Special Emergency Leave (SEL)'],
  
  
    ]);
}
}
