<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDataAccuracyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LeaveType $leaveType;
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $this->leaveType = LeaveType::create([
            'code' => 'SL',
            'leave_type_name' => 'Sick Leave',
        ]);
    }

    private function department(string $name): Department
    {
        return Department::create(['department_name' => $name, 'level' => 'JHS']);
    }

    private function employee(string $lastName, string $status = 'active', ?Department $department = null): EmployeeRecord
    {
        $number = ++$this->sequence;
        $user = User::create([
            'email' => "employee{$number}@example.test",
            'password' => 'password',
            'role' => 'employee',
        ]);

        return EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => sprintf('ENHS-%03d', $number),
            'first_name' => 'Test',
            'last_name' => $lastName,
            'sex' => 'Female',
            'employment_status' => $status,
            'date_hired' => '2020-01-01',
            'department_id' => $department?->department_id,
        ]);
    }

    private function leave(
        EmployeeRecord $employee,
        string $start,
        string $end,
        int $days,
        string $status,
        string $filed = '2026-01-01'
    ): LeaveApplication {
        return LeaveApplication::create([
            'employee_id' => $employee->employee_id,
            'leave_type_id' => $this->leaveType->leave_type_id,
            'date_filed' => $filed,
            'number_of_days' => $days,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => 'Test leave',
            'final_status' => $status,
        ]);
    }

    private function balance(EmployeeRecord $employee, array $values): LeaveBalance
    {
        return LeaveBalance::create(['employee_id' => $employee->employee_id] + $values);
    }

    private function getAsAdmin(string $uri)
    {
        return $this->actingAs($this->admin, 'sanctum')->getJson($uri);
    }

    private function assertDays(float $expected, mixed $actual, string $message = ''): void
    {
        $this->assertEqualsWithDelta($expected, (float) $actual, 0.0005, $message);
    }

    /** Leave Summary: preserves the intended rules; guards against regressions. */
    public function test_leave_summary_uses_leave_date_overlap_and_consistent_totals(): void
    {
        $deptA = $this->department('Department A');
        $deptB = $this->department('Department B');
        $active = $this->employee('Active', 'active', $deptA);
        $inactive = $this->employee('Inactive', 'inactive', $deptB);
        $archived = $this->employee('Archived', 'active', $deptA);

        // Included: starts before the period but overlaps it.
        $this->leave($active, '2026-02-27', '2026-03-02', 4, 'approved');
        // Included: inside the period.
        $this->leave($active, '2026-03-10', '2026-03-11', 2, 'pending');
        // Included: starts on the last day of the period.
        $this->leave($inactive, '2026-03-31', '2026-04-02', 3, 'disapproved');
        // Excluded: filed inside the period, but the leave dates are outside it.
        $this->leave($active, '2026-04-01', '2026-04-02', 2, 'approved', '2026-03-15');
        // Excluded: filed inside the period, but the leave ended before it.
        $this->leave($active, '2026-02-01', '2026-02-05', 5, 'approved', '2026-03-05');
        // Included only while archived employees are in scope.
        $this->leave($archived, '2026-03-05', '2026-03-05', 1, 'approved');
        // Excluded: soft-deleted application.
        $this->leave($active, '2026-03-20', '2026-03-21', 2, 'approved')->delete();
        $archived->delete();

        $query = '?start_date=2026-03-01&end_date=2026-03-31';

        $all = $this->getAsAdmin("/api/reports/leave-summary{$query}&include_inactive=1")->assertOk();
        $this->assertSame(4, $all->json('totals.applications'));
        $this->assertSame(2, $all->json('totals.approved'));
        $this->assertSame(1, $all->json('totals.pending'));
        $this->assertSame(1, $all->json('totals.disapproved'));
        $this->assertSame(0, $all->json('totals.other'));
        $this->assertSame('10.000', $all->json('totals.total_days'));
        $this->assertSame('5.000', $all->json('totals.approved_days'));

        // Department rows and leave-type breakdowns add up to the grand totals.
        $rows = collect($all->json('summary'));
        $this->assertSame(4, $rows->sum('total'));
        $this->assertSame(
            $all->json('totals.applications'),
            $rows->sum(fn ($row) => array_sum($row['leave_types']))
        );
        $this->assertDays(10, $rows->sum(fn ($row) => (float) $row['total_days']));
        $this->assertDays(5, $rows->sum(fn ($row) => (float) $row['approved_days']));

        $activeOnly = $this->getAsAdmin("/api/reports/leave-summary{$query}&include_inactive=0")->assertOk();
        $this->assertSame(2, $activeOnly->json('totals.applications'));
        $this->assertSame(1, $activeOnly->json('totals.approved'));
        $this->assertSame(1, $activeOnly->json('totals.pending'));
        $this->assertSame(0, $activeOnly->json('totals.disapproved'));
        $this->assertSame('6.000', $activeOnly->json('totals.total_days'));
        $this->assertSame('4.000', $activeOnly->json('totals.approved_days'));

        // With no dates every non-deleted application counts (soft-deleted one excluded).
        $undated = $this->getAsAdmin('/api/reports/leave-summary?include_inactive=1')->assertOk();
        $this->assertSame(6, $undated->json('totals.applications'));
    }

    /** Leave Credits: every non-archived employee is listed; a missing record is not a zero balance. */
    public function test_leave_credits_lists_all_employees_and_flags_missing_balance_records(): void
    {
        $withBalance = $this->employee('Funded');
        $inactive = $this->employee('Inactive', 'inactive');
        $noRecord = $this->employee('Norecord');
        $archived = $this->employee('Archived');

        $this->balance($withBalance, [
            'vacation_earned' => 15, 'sick_earned' => 15,
            'vacation_balance' => 10, 'sick_balance' => 5,
            'service_credits' => 2.5, 'used_leave' => 3,
        ]);
        $this->balance($inactive, [
            'vacation_balance' => 1, 'sick_balance' => 1,
            'service_credits' => 0, 'used_leave' => 0,
        ]);
        $this->balance($archived, ['vacation_balance' => 99, 'sick_balance' => 99]);
        $archived->delete();

        // A pending application is not a completed deduction.
        $this->leave($withBalance, '2026-03-10', '2026-03-12', 3, 'pending');

        $response = $this->getAsAdmin('/api/reports/leave-credits')->assertOk();
        $rows = collect($response->json('employees'))->keyBy('employee_id');

        $this->assertCount(3, $rows);
        $this->assertFalse($rows->has($archived->employee_id));
        $this->assertSame(3, $response->json('totals.employees'));
        $this->assertSame(1, $response->json('totals.employees_without_balance_record'));

        $funded = $rows[$withBalance->employee_id];
        $this->assertTrue($funded['has_balance_record']);
        $this->assertDays(10, $funded['vacation_balance']);
        $this->assertDays(5, $funded['sick_balance']);
        $this->assertDays(2.5, $funded['service_credits']);
        $this->assertDays(17.5, $funded['total_remaining']);
        $this->assertDays(3, $funded['used_leave']);

        $missing = $rows[$noRecord->employee_id];
        $this->assertFalse($missing['has_balance_record']);
        $this->assertDays(0, $missing['total_remaining']);

        $this->assertTrue($rows[$inactive->employee_id]['has_balance_record']);

        // Aggregate totals equal the sum of the employee rows.
        $this->assertDays(11, $response->json('totals.vacation_balance'));
        $this->assertDays(6, $response->json('totals.sick_balance'));
        $this->assertDays(2.5, $response->json('totals.service_credits'));
        $this->assertDays(19.5, $response->json('totals.total_remaining'));
        $this->assertDays(3, $response->json('totals.used_leave'));
        foreach (['vacation_balance', 'sick_balance', 'service_credits', 'total_remaining', 'used_leave'] as $field) {
            $this->assertDays($rows->sum($field), $response->json("totals.{$field}"), $field);
        }
    }

    /** The report endpoint and the Leave Balances page must agree on the same data. */
    public function test_leave_credits_report_matches_leave_balances_endpoint(): void
    {
        $one = $this->employee('One');
        $two = $this->employee('Two');
        $this->employee('Three'); // no balance record

        $this->balance($one, [
            'vacation_balance' => 12.5, 'sick_balance' => 7, 'service_credits' => 1.25, 'used_leave' => 4,
        ]);
        $this->balance($two, [
            'vacation_balance' => 0, 'sick_balance' => 0, 'service_credits' => 0, 'used_leave' => 0,
        ]);

        $report = collect($this->getAsAdmin('/api/reports/leave-credits')->assertOk()->json('employees'))
            ->keyBy('employee_id');
        $balances = collect($this->getAsAdmin('/api/leave-balances')->assertOk()->json())
            ->keyBy('employee_id');

        $this->assertSame($balances->keys()->sort()->values()->all(), $report->keys()->sort()->values()->all());
        foreach ($balances as $employeeId => $balance) {
            foreach (['vacation_balance', 'sick_balance', 'service_credits', 'used_leave'] as $field) {
                $this->assertDays((float) $balance[$field], $report[$employeeId][$field], "{$field} for employee {$employeeId}");
            }
            // The Leave Balances endpoint marks a missing record with a null balance_id.
            $this->assertSame($balance['balance_id'] !== null, $report[$employeeId]['has_balance_record']);
        }
    }
}
