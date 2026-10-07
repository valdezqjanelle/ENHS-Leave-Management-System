<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    private const OMNIBUS = 'Omnibus Rules Implementing E.O. No. 292';

    /**
     * Standard CS Form 6 leave types, based on the "Instructions and Requirements"
     * page of CS Form No. 6.
     *
     * - requirements: the documents the employee must submit.
     * - instructions: the filing rules (when and how to file, duration limits).
     *
     * The `id` follows the order of the CS Form 6 checkboxes (1-13), then "Other purposes"
     * (14). Custom leave types added by the admin continue from 15.
     *
     * The `code` is used by LeaveApplication.vue to decide which extra fields to show,
     * so keep VL, SL, STL, SLBW and OP unchanged.
     */
    private function leaveTypes(): array
    {
        $omnibus = self::OMNIBUS;

        return [
            [
                'id' => 1,
                'code' => 'VL',
                'name' => 'Vacation Leave',
                'legal_basis' => "Sec. 51, Rule XVI, {$omnibus}",
                'requirements' => 'A clearance from money, property and work-related accountabilities if the leave is 30 calendar days or more.',
                'instructions' => 'Application shall be filed five (5) days in advance, whenever possible, of the effective date of such leave. Vacation leave within the Philippines or abroad shall be indicated in the form for purposes of securing travel authority and completing clearance from money and work accountabilities.',
            ],
            [
                'id' => 2,
                'code' => 'FL',
                'name' => 'Mandatory/Forced Leave',
                'legal_basis' => "Sec. 25, Rule XVI, {$omnibus}",
                'requirements' => 'No supporting documents required.',
                'instructions' => 'Annual five-day vacation leave shall be forfeited if not taken during the year. In case the scheduled leave has been cancelled in the exigency of the service by the head of agency, it shall no longer be deducted from the accumulated vacation leave. Availment of one (1) day or more Vacation Leave (VL) shall be considered for complying with the mandatory/forced leave, subject to the conditions under Section 25, Rule XVI of the Omnibus Rules Implementing E.O. No. 292.',
            ],
            [
                'id' => 3,
                'code' => 'SL',
                'name' => 'Sick Leave',
                'legal_basis' => "Sec. 43, Rule XVI, {$omnibus}",
                'requirements' => 'A medical certificate if filed in advance or exceeding five (5) days (or an affidavit if no medical consultation was made), and a clearance if the leave is 30 calendar days or more.',
                'instructions' => 'Application shall be filed immediately upon the employee\'s return from such leave.',
            ],
            [
                'id' => 4,
                'code' => 'ML',
                'name' => 'Maternity Leave',
                'legal_basis' => 'R.A. No. 11210 / IRR issued by CSC, DOLE and SSS',
                'requirements' => 'Proof of pregnancy (such as an ultrasound or a doctor\'s certificate with the expected date of delivery), CS Form No. 6a if needed, and a clearance from money, property and work-related accountabilities.',
                'instructions' => 'Maternity leave is up to 105 days. Seconded female employees shall enjoy maternity leave with full pay in the recipient agency.',
            ],
            [
                'id' => 5,
                'code' => 'PL',
                'name' => 'Paternity Leave',
                'legal_basis' => 'R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended',
                'requirements' => 'Proof of the child\'s delivery, such as a birth certificate, medical certificate and marriage contract.',
                'instructions' => 'Paternity leave is up to seven (7) days.',
            ],
            [
                'id' => 6,
                'code' => 'SPL',
                'name' => 'Special Privilege Leave',
                'legal_basis' => "Sec. 21, Rule XVI, {$omnibus}",
                'requirements' => 'No supporting documents required.',
                'instructions' => 'Special privilege leave is up to three (3) days. It shall be filed/approved at least one (1) week prior to availment, except on emergency cases. Special privilege leave within the Philippines or abroad shall be indicated in the form for purposes of securing travel authority and completing clearance from money and work accountabilities.',
            ],
            [
                'id' => 7,
                'code' => 'SOLO',
                'name' => 'Solo Parent Leave',
                'legal_basis' => 'RA No. 8972 / CSC MC No. 8, s. 2004',
                'requirements' => 'An updated Solo Parent Identification Card.',
                'instructions' => 'Solo parent leave is up to seven (7) days. It shall be filed in advance or whenever possible five (5) days before going on such leave.',
            ],
            [
                'id' => 8,
                'code' => 'STL',
                'name' => 'Study Leave',
                'legal_basis' => "Sec. 68, Rule XVI, {$omnibus}",
                'requirements' => 'A contract between the agency head (or authorized representative) and the employee, and a clearance if the leave is 30 calendar days or more.',
                'instructions' => 'Study leave is up to six (6) months. The employee shall meet the agency\'s internal requirements, if any.',
            ],
            [
                'id' => 9,
                'code' => 'VAWC',
                'name' => '10-Day VAWC Leave',
                'legal_basis' => 'RA No. 9262 / CSC MC No. 15, s. 2005',
                'requirements' => 'A Barangay Protection Order, a court TPO/PPO, a certification that one has been applied for, or a police report with a medical certificate.',
                'instructions' => 'VAWC leave is up to ten (10) days. It shall be filed in advance or immediately upon the woman employee\'s return from such leave.',
            ],
            [
                'id' => 10,
                'code' => 'RP',
                'name' => 'Rehabilitation Privilege',
                'legal_basis' => "Sec. 55, Rule XVI, {$omnibus}",
                'requirements' => 'A letter request, a medical certificate, a police report if any, and a government physician\'s written concurrence if the attending physician is a private practitioner.',
                'instructions' => 'Rehabilitation privilege is up to six (6) months. Application shall be made within one (1) week from the time of the accident except when a longer period is warranted.',
            ],
            [
                'id' => 11,
                'code' => 'SLBW',
                'name' => 'Special Leave Benefits for Women',
                'legal_basis' => 'RA No. 9710 / CSC MC No. 25, s. 2010',
                'requirements' => 'A medical certificate from the attending surgeon with a clinical summary of the gynecological surgery and the estimated period of recuperation.',
                'instructions' => 'Special leave benefits for women is up to two (2) months. The application may be filed in advance, that is, at least five (5) days prior to the scheduled date of the gynecological surgery that will be undergone by the employee. In case of emergency, the application for special leave shall be filed immediately upon the employee\'s return but during confinement the agency shall be notified of said surgery.',
            ],
            [
                'id' => 12,
                'code' => 'SEL',
                'name' => 'Special Emergency (Calamity) Leave ',
                'legal_basis' => 'CSC MC No. 2, s. 2012, as amended',
                'requirements' => 'Proof that the employee\'s residence is in the declared calamity area, as verified by the head of office.',
                'instructions' => 'Can be applied for a maximum of five (5) straight working days or on a staggered basis within thirty (30) days from the actual occurrence of the natural calamity/disaster, and said privilege shall be enjoyed once a year, not in every instance of calamity or disaster. The head of office shall take full responsibility for the grant of special emergency leave and verification of the employee\'s eligibility to be granted thereof.',
            ],
            [
                'id' => 13,
                'code' => 'AL',
                'name' => 'Adoption Leave',
                'legal_basis' => 'R.A. No. 8552',
                'requirements' => 'An authenticated copy of the Pre-Adoptive Placement Authority issued by the Department of Social Welfare and Development (DSWD).',
                'instructions' => 'Application for adoption leave shall be filed together with the required document.',
            ],
            [
                'id' => 14,
                // Catch-all type for Monetization, Terminal Leave and other purposes.
                'code' => 'OP',
                'name' => 'Other purposes',
                'legal_basis' => null,
                'requirements' => 'A letter request stating valid reasons for monetization, or proof of resignation, retirement or separation from the service (with a clearance) for terminal leave.',
                'instructions' => 'Monetization applies to fifty percent (50%) or more of the accumulated leave credits, while terminal leave applies to employees who are resigning, retiring or otherwise separating from the service.',
            ],
        ];
    }

    /**
     * Run the database seeds.
     *
     * Safe to run more than once: existing rows are matched by code, then by name,
     * so nothing is duplicated. For the standard leave types the legal basis,
     * requirements and instructions are refreshed from CS Form 6; an existing code
     * is never changed. Custom leave types added by the admin (e.g. Local Leave)
     * are left alone.
     */
    public function run(): void
    {
        $now = now();

        foreach ($this->leaveTypes() as $type) {
            $existing = DB::table('leave_types')
                ->where('code', $type['code'])
                ->orWhereRaw('LOWER(leave_type_name) LIKE ?', [strtolower($type['name']) . '%'])
                ->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [$type['code']])
                ->first();

            if ($existing) {
                $updates = [
                    'legal_basis'  => $type['legal_basis'],
                    'requirements' => $type['requirements'],
                    'instructions' => $type['instructions'],
                    'updated_at'   => $now,
                ];

                if (empty($existing->code)) {
                    $updates['code'] = $type['code'];
                }

                DB::table('leave_types')
                    ->where('leave_type_id', $existing->leave_type_id)
                    ->update($updates);

                continue;
            }

            $row = [
                'code'            => $type['code'],
                'leave_type_name' => $type['name'],
                'legal_basis'     => $type['legal_basis'],
                'requirements'    => $type['requirements'],
                'instructions'    => $type['instructions'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            // Use the CS Form 6 position as the id, unless another row already holds it
            // (run the reorder migration first to avoid that).
            if (!DB::table('leave_types')->where('leave_type_id', $type['id'])->exists()) {
                $row['leave_type_id'] = $type['id'];
            }

            DB::table('leave_types')->insert($row);
        }

        // Explicit ids were inserted above, so move the Postgres sequence past them.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('leave_types', 'leave_type_id'), "
                . "GREATEST((SELECT COALESCE(MAX(leave_type_id), 1) FROM leave_types), 14))"
            );
        }
    }
}