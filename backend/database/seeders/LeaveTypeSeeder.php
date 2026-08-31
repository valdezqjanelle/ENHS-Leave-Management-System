<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'code' => 'VL',
                'leave_type_name' => 'Vacation Leave',
                'legal_basis' =>
                    'Normally filed at least five days in advance whenever possible. Applications involving travel abroad must indicate the necessary travel and clearance information.',
            ],
            [
                'code' => 'FL',
                'leave_type_name' => 'Mandatory/Forced Leave',
                'legal_basis' =>
                    'Annual five-day mandatory leave chargeable against accumulated Vacation Leave credits, subject to applicable Civil Service rules.',
            ],
            [
                'code' => 'SL',
                'leave_type_name' => 'Sick Leave',
                'legal_basis' =>
                    'Must be filed immediately upon return. Applications filed in advance or exceeding five days should be supported by a medical certificate. An affidavit may be required when medical consultation was not obtained.',
            ],
            [
                'code' => 'ML',
                'leave_type_name' => 'Maternity Leave',
                'legal_basis' =>
                    'Up to 105 days, subject to proof of pregnancy and other documentary requirements under applicable maternity leave rules.',
            ],
            [
                'code' => 'PL',
                'leave_type_name' => 'Paternity Leave',
                'legal_basis' =>
                    'Up to 7 days, subject to proof of childbirth, birth certificate, medical certificate, marriage contract, and other applicable documentary requirements.',
            ],
            [
                'code' => 'SPL',
                'leave_type_name' => 'Special Privilege Leave',
                'legal_basis' =>
                    'Up to 3 days. Normally filed at least one week before availment, except in emergency cases.',
            ],
            [
                'code' => 'SOLO',
                'leave_type_name' => 'Solo Parent Leave',
                'legal_basis' =>
                    'Up to 7 days, subject to an updated and valid Solo Parent Identification Card and applicable rules.',
            ],
            [
                'code' => 'STL',
                'leave_type_name' => 'Study Leave',
                'legal_basis' =>
                    'Up to 6 months, subject to agency requirements and an approved agreement between the employee and the agency head or authorized representative.',
            ],
            [
                'code' => 'VAWC',
                'leave_type_name' => 'VAWC Leave',
                'legal_basis' =>
                    'Up to 10 days. The application must be supported by an applicable Barangay Protection Order, Temporary or Permanent Protection Order, certification, police report, medical certificate, or other permitted supporting document.',
            ],
            [
                'code' => 'RL',
                'leave_type_name' => 'Rehabilitation Leave',
                'legal_basis' =>
                    'Up to 6 months for injuries resulting from an accident sustained in the performance of duty, subject to medical certification and other relevant supporting records.',
            ],
            [
                'code' => 'SLBW',
                'leave_type_name' => 'Special Leave Benefits for Women',
                'legal_basis' =>
                    'Up to 2 months for qualified gynecological surgery, subject to medical certification and applicable documentary requirements.',
            ],
            [
                'code' => 'SEL',
                'leave_type_name' => 'Special Emergency (Calamity) Leave',
                'legal_basis' =>
                    'Up to 5 days, subject to verification of eligibility and proof that the employee is affected by a declared calamity or disaster.',
            ],
            [
                'code' => 'MON',
                'leave_type_name' => 'Monetization of Leave Credits',
                'legal_basis' =>
                    'Application for monetization of accumulated leave credits must comply with applicable Civil Service and agency requirements.',
            ],
            [
                'code' => 'TL',
                'leave_type_name' => 'Terminal Leave',
                'legal_basis' =>
                    'Applicable upon resignation, retirement, or separation from government service, subject to the required clearance and supporting documents.',
            ],
            [
                'code' => 'AL',
                'leave_type_name' => 'Adoption Leave',
                'legal_basis' =>
                    'Subject to an authenticated copy of the Pre-Adoptive Placement Authority issued by the Department of Social Welfare and Development and other applicable requirements.',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->updateOrInsert(
                [
                    'code' => $leaveType['code'],
                ],
                [
                    'leave_type_name' =>
                        $leaveType['leave_type_name'],
                    'legal_basis' =>
                        $leaveType['legal_basis'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}