<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LeaveType $vacation;
    private LeaveType $sick;
    private Department $math;
    private Department $science;
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create(['email' => 'admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $this->vacation = LeaveType::create(['code' => 'VL', 'leave_type_name' => 'Vacation Leave']);
        $this->sick = LeaveType::create(['code' => 'SL', 'leave_type_name' => 'Sick Leave']);
        $this->math = Department::create(['department_name' => 'Mathematics', 'level' => 'JHS']);
        $this->science = Department::create(['department_name' => 'Science', 'level' => 'JHS']);
    }

    private function employee(string $first, string $last, ?Department $department, string $status = 'active'): EmployeeRecord
    {
        $number = ++$this->sequence;
        $user = User::create(['email' => "employee{$number}@example.test", 'password' => 'password', 'role' => 'employee']);

        return EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => sprintf('ENHS-%03d', $number),
            'first_name' => $first,
            'last_name' => $last,
            'sex' => 'Female',
            'employment_status' => $status,
            'date_hired' => '2020-01-01',
            'department_id' => $department?->department_id,
        ]);
    }

    private function leave(EmployeeRecord $employee, LeaveType $type, string $status, int $days, string $start, string $filed, array $extra = []): LeaveApplication
    {
        return LeaveApplication::create(array_merge([
            'employee_id' => $employee->employee_id,
            'leave_type_id' => $type->leave_type_id,
            'date_filed' => $filed,
            'number_of_days' => $days,
            'start_date' => $start,
            'end_date' => $start,
            'reason' => 'Test leave',
            'final_status' => $status,
        ], $extra));
    }

    private function history(string $query = '')
    {
        return $this->actingAs($this->admin, 'sanctum')->getJson('/api/reports/leave-history' . ($query ? "?{$query}" : ''));
    }

    private function summary(string $query = '')
    {
        return $this->actingAs($this->admin, 'sanctum')->getJson('/api/reports/leave-summary' . ($query ? "?{$query}" : ''));
    }

    /** Ana (Math), Ben (Science), Cara (no department) and an archived Eve (Math); six applications. */
    private function seed(): array
    {
        $ana = $this->employee('Ana', 'Cruz', $this->math);
        $ben = $this->employee('Ben', 'Reyes', $this->science);
        $cara = $this->employee('Cara', 'Santos', null);
        $eve = $this->employee('Eve', 'Lopez', $this->math);

        $approved = $this->leave($ana, $this->vacation, 'approved', 2, '2026-03-10', '2026-02-20', [
            'recommendation_status' => 'approved',
            'days_with_pay' => 2,
            'days_without_pay' => 0,
            'admin_remarks' => 'Cleared by the principal',
            'reviewed_at' => '2026-02-22 09:00:00',
        ]);
        $this->leave($ana, $this->sick, 'pending', 1, '2026-04-05', '2026-04-01');
        $this->leave($ben, $this->vacation, 'approved', 3, '2026-03-20', '2026-03-01');
        $this->leave($ben, $this->sick, 'disapproved', 1, '2026-05-02', '2026-04-28', [
            'recommendation_status' => 'disapproved',
            'disapproval_reason' => 'Insufficient balance',
        ]);
        $this->leave($cara, $this->sick, 'approved', 4, '2026-03-25', '2026-03-24');
        $this->leave($eve, $this->vacation, 'approved', 1, '2026-03-30', '2026-03-15');
        $eve->delete();

        return compact('ana', 'ben', 'cara', 'eve', 'approved');
    }

    public function test_requires_an_admin(): void
    {
        $this->getJson('/api/reports/leave-history')->assertUnauthorized();

        $employee = User::create(['email' => 'staff@example.test', 'password' => 'password', 'role' => 'employee']);
        $this->actingAs($employee, 'sanctum')->getJson('/api/reports/leave-history')->assertForbidden();
    }

    public function test_returns_one_record_per_application_with_recorded_fields_only(): void
    {
        $seeded = $this->seed();

        $response = $this->history()->assertOk();

        $this->assertSame('leave-history-v1', $response->json('report_version'));
        $this->assertSame(6, $response->json('totals.records'));
        $this->assertCount(6, $response->json('records'));
        $this->assertSame(4, $response->json('totals.approved'));
        $this->assertSame(1, $response->json('totals.pending'));
        $this->assertSame(1, $response->json('totals.disapproved'));
        $this->assertSame('12.000', $response->json('totals.requested_days'));
        $this->assertSame('10.000', $response->json('totals.approved_days'));

        $record = collect($response->json('records'))->firstWhere('leave_id', $seeded['approved']->leave_id);
        $this->assertSame('Cruz, Ana', $record['employee_name']);
        $this->assertSame('Mathematics', $record['department']);
        $this->assertSame('Vacation Leave', $record['leave_type']);
        $this->assertSame('2026-02-20', $record['date_filed']);
        $this->assertSame('2026-03-10', $record['start_date']);
        $this->assertSame('2026-03-10', $record['end_date']);
        $this->assertSame('2.000', $record['requested_days']);
        $this->assertSame('approved', $record['status']);
        $this->assertSame('approved', $record['recommendation_status']);
        $this->assertEquals(2, $record['days_with_pay']);
        $this->assertSame('Cleared by the principal', $record['admin_remarks']);
        $this->assertNotNull($record['status_changed_at']);

        // Nothing is invented: unrecorded approval details stay empty, and no Division Office field exists.
        $pending = collect($response->json('records'))->firstWhere('status', 'pending');
        $this->assertNull($pending['status_changed_at']);
        $this->assertNull($pending['disapproval_reason']);
        $this->assertNull($pending['admin_remarks']);
        $this->assertArrayNotHasKey('division_office_outcome', $pending);
        $this->assertContains('Division Office outcome', $response->json('filters.not_recorded'));
    }

    public function test_records_are_newest_leave_first_and_flag_archived_and_unassigned_employees(): void
    {
        $this->seed();

        $records = collect($this->history()->assertOk()->json('records'));

        $this->assertSame($records->pluck('start_date')->sortDesc()->values()->all(), $records->pluck('start_date')->all());
        $this->assertSame('archived', $records->firstWhere('employee_name', 'Lopez, Eve')['employee_state']);
        $this->assertSame('Unassigned', $records->firstWhere('employee_name', 'Santos, Cara')['department']);
    }

    public function test_filters_match_leave_summary_exactly(): void
    {
        $this->seed();

        $queries = [
            '',
            'status=approved',
            "department_id={$this->math->department_id}",
            'department_id=unassigned',
            "leave_type_id={$this->sick->leave_type_id}",
            'search=ana',
            'start_date=2026-03-15&end_date=2026-03-31',
            "department_id={$this->math->department_id}&status=approved&include_inactive=0",
            "status=pending&leave_type_id={$this->sick->leave_type_id}",
        ];

        foreach ($queries as $query) {
            $history = $this->history($query)->assertOk();
            $summary = $this->summary($query)->assertOk();

            $this->assertSame($summary->json('totals.applications'), $history->json('totals.records'), "records: {$query}");
            $this->assertSame($summary->json('totals.approved'), $history->json('totals.approved'), "approved: {$query}");
            $this->assertSame($summary->json('totals.pending'), $history->json('totals.pending'), "pending: {$query}");
            $this->assertSame($summary->json('totals.disapproved'), $history->json('totals.disapproved'), "disapproved: {$query}");
            $this->assertSame($summary->json('totals.total_days'), $history->json('totals.requested_days'), "days: {$query}");
            $this->assertSame($summary->json('totals.approved_days'), $history->json('totals.approved_days'), "approved days: {$query}");
            $this->assertSame($history->json('totals.records'), count($history->json('records')), "count: {$query}");
            $this->assertSame($summary->json('filters.search'), $history->json('filters.search'), "echo: {$query}");
        }
    }

    public function test_period_filter_uses_leave_dates(): void
    {
        $this->seed();

        $march = $this->history('start_date=2026-03-01&end_date=2026-03-31')->assertOk();
        $this->assertSame(4, $march->json('totals.records'));
        $this->assertSame('2026-03-01', $march->json('filters.start_date'));

        $this->history('start_date=2026-06-01&end_date=2026-06-30')->assertOk()
            ->assertJsonPath('totals.records', 0)
            ->assertJsonPath('records', []);
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->seed();

        $this->history('status=cancelled')->assertStatus(422)->assertJsonValidationErrors('status');
        $this->history('department_id=999999')->assertStatus(422)->assertJsonValidationErrors('department_id');
        $this->history('start_date=2026-03-31&end_date=2026-03-01')->assertStatus(422)->assertJsonValidationErrors('end_date');
    }

    public function test_leave_summary_reports_applications_filed_per_month_by_current_status(): void
    {
        $this->seed();

        $response = $this->summary()->assertOk();
        $monthly = collect($response->json('monthly'))->keyBy('month');

        $this->assertSame(['2026-02', '2026-03', '2026-04'], $monthly->keys()->all());
        $this->assertSame(1, $monthly['2026-02']['submitted']);
        $this->assertSame(1, $monthly['2026-02']['approved']);
        $this->assertSame(3, $monthly['2026-03']['submitted']);
        $this->assertSame(3, $monthly['2026-03']['approved']);
        $this->assertSame(2, $monthly['2026-04']['submitted']);
        $this->assertSame(1, $monthly['2026-04']['pending']);
        $this->assertSame(1, $monthly['2026-04']['disapproved']);

        // Every application is counted exactly once, and the buckets add up to what was submitted.
        $this->assertSame($response->json('totals.applications'), $monthly->sum('submitted'));
        foreach ($monthly as $month) {
            $this->assertSame(
                $month['submitted'],
                $month['approved'] + $month['pending'] + $month['disapproved'] + $month['other']
            );
        }

        // The trend follows the same filters as the rest of the report.
        $filtered = collect($this->summary('status=approved')->json('monthly'))->keyBy('month');
        $this->assertSame(0, $filtered->sum('pending'));
        $this->assertSame(4, $filtered->sum('submitted'));
    }
}
