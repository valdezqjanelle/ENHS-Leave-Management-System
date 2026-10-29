<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFiltersTest extends TestCase
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

    private function employee(string $first, string $last, ?Department $department, string $status = 'active', ?string $middle = null): EmployeeRecord
    {
        $number = ++$this->sequence;
        $user = User::create(['email' => "employee{$number}@example.test", 'password' => 'password', 'role' => 'employee']);

        return EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => sprintf('ENHS-%03d', $number),
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'sex' => 'Female',
            'employment_status' => $status,
            'date_hired' => '2020-01-01',
            'department_id' => $department?->department_id,
        ]);
    }

    private function leave(EmployeeRecord $employee, LeaveType $type, string $status, int $days = 1, string $start = '2026-03-10'): LeaveApplication
    {
        return LeaveApplication::create([
            'employee_id' => $employee->employee_id,
            'leave_type_id' => $type->leave_type_id,
            'date_filed' => '2026-01-01',
            'number_of_days' => $days,
            'start_date' => $start,
            'end_date' => $start,
            'reason' => 'Test leave',
            'final_status' => $status,
        ]);
    }

    private function summary(string $query = '')
    {
        return $this->actingAs($this->admin, 'sanctum')->getJson('/api/reports/leave-summary' . ($query ? "?{$query}" : ''));
    }

    /**
     * Ana (Math), Ben (Science), Cara (no department), Dan (Math, inactive),
     * and Eve (Math) who is archived. Ten applications across both leave types and all statuses.
     */
private function setUpTestData(): array
{
        $ana = $this->employee('Ana', 'Cruz', $this->math, 'active', 'Maria');
        $ben = $this->employee('Ben', 'Reyes', $this->science);
        $cara = $this->employee('Cara', 'Santos', null);
        $dan = $this->employee('Dan', 'Lopez', $this->math, 'inactive');
        $eve = $this->employee('Eve', "O'Neil", $this->math);

        $this->leave($ana, $this->vacation, 'approved', 2);
        $this->leave($ana, $this->sick, 'pending', 1);
        $this->leave($ben, $this->vacation, 'approved', 3);
        $this->leave($ben, $this->sick, 'disapproved', 1);
        $this->leave($cara, $this->sick, 'approved', 4);
        $this->leave($cara, $this->vacation, 'pending', 2);
        $this->leave($dan, $this->vacation, 'approved', 5);
        $this->leave($dan, $this->sick, 'pending', 1);
        $this->leave($eve, $this->vacation, 'approved', 1);
        $this->leave($eve, $this->sick, 'disapproved', 2);
        $eve->delete(); // archived (soft deleted)

        return compact('ana', 'ben', 'cara', 'dan', 'eve');
    }

    /** Cards, department rows and leave-type breakdowns must describe the same applications. */
    private function assertConsistent($response): void
    {
        $totals = $response->json('totals');
        $rows = collect($response->json('summary'));

        $this->assertSame($totals['applications'], $rows->sum('total'));
        $this->assertSame($totals['approved'], $rows->sum('approved'));
        $this->assertSame($totals['pending'], $rows->sum('pending'));
        $this->assertSame($totals['disapproved'], $rows->sum('disapproved'));
        $this->assertSame(
            $totals['applications'],
            $totals['approved'] + $totals['pending'] + $totals['disapproved'] + $totals['other']
        );
        $this->assertSame($totals['applications'], $rows->sum(fn ($row) => array_sum($row['leave_types'])));
        $this->assertEqualsWithDelta((float) $totals['total_days'], $rows->sum(fn ($row) => (float) $row['total_days']), 0.0005);
        $this->assertEqualsWithDelta((float) $totals['approved_days'], $rows->sum(fn ($row) => (float) $row['approved_days']), 0.0005);
    }

    public function test_no_filters_keeps_the_existing_totals(): void
    {
        $this->setUpTestData();

        $response = $this->summary()->assertOk();

        $this->assertSame(10, $response->json('totals.applications'));
        $this->assertSame(5, $response->json('totals.approved'));
        $this->assertSame(3, $response->json('totals.pending'));
        $this->assertSame(2, $response->json('totals.disapproved'));
        $this->assertNull($response->json('filters.department_id'));
        $this->assertNull($response->json('filters.status'));
        $this->assertNull($response->json('filters.leave_type_id'));
        $this->assertNull($response->json('filters.search'));
        $this->assertConsistent($response);
    }

    public function test_department_filter_limits_every_total(): void
    {
        $this->setUpTestData();

        // Mathematics = Ana (2) + Dan (2) + archived Eve (2) while inactive/archived are included.
        $math = $this->summary("department_id={$this->math->department_id}")->assertOk();
        $this->assertSame(6, $math->json('totals.applications'));
        $this->assertSame(['Mathematics'], collect($math->json('summary'))->pluck('department')->all());
        $this->assertSame((string) $this->math->department_id, $math->json('filters.department_id'));
        $this->assertConsistent($math);

        $science = $this->summary("department_id={$this->science->department_id}")->assertOk();
        $this->assertSame(2, $science->json('totals.applications'));
        $this->assertConsistent($science);

        $unassigned = $this->summary('department_id=unassigned')->assertOk();
        $this->assertSame(2, $unassigned->json('totals.applications'));
        $this->assertSame(['Unassigned'], collect($unassigned->json('summary'))->pluck('department')->all());
        $this->assertConsistent($unassigned);
    }

    public function test_status_and_leave_type_filters(): void
    {
        $this->setUpTestData();

        $approved = $this->summary('status=approved')->assertOk();
        $this->assertSame(5, $approved->json('totals.applications'));
        $this->assertSame(5, $approved->json('totals.approved'));
        $this->assertSame(0, $approved->json('totals.pending'));
        $this->assertSame(0, $approved->json('totals.disapproved'));
        $this->assertSame('15.000', $approved->json('totals.total_days'));
        $this->assertSame('15.000', $approved->json('totals.approved_days'));
        $this->assertConsistent($approved);

        $sick = $this->summary("leave_type_id={$this->sick->leave_type_id}")->assertOk();
        $this->assertSame(5, $sick->json('totals.applications'));
        $this->assertSame([['Sick Leave']], collect($sick->json('summary'))->map(fn ($row) => array_keys($row['leave_types']))->unique()->values()->all());
        $this->assertConsistent($sick);

        $combined = $this->summary("status=pending&leave_type_id={$this->sick->leave_type_id}")->assertOk();
        $this->assertSame(2, $combined->json('totals.applications')); // Ana's and Dan's pending sick leave
        $this->assertConsistent($combined);
    }

    public function test_employee_search_matches_name_parts_in_any_order_case_insensitively(): void
    {
        $this->setUpTestData();

        foreach (['cruz', 'CRUZ', 'ana', 'ana cruz', 'cruz, ana', 'maria', 'ANA   CRUZ'] as $query) {
            $response = $this->summary('search=' . rawurlencode($query))->assertOk();
            $this->assertSame(2, $response->json('totals.applications'), "search: {$query}");
            $this->assertConsistent($response);
        }

        // Partial match on the archived employee, who stays visible while archived are included.
        $this->assertSame(2, $this->summary("search=o'neil")->json('totals.applications'));
        $this->assertSame(2, $this->summary('search=ve')->json('totals.applications')); // "Eve" only
        $this->assertSame(0, $this->summary('search=ve&include_inactive=0')->json('totals.applications'));

        // Every word has to match the same employee.
        $this->assertSame(0, $this->summary('search=' . rawurlencode('ana reyes'))->json('totals.applications'));
        $this->assertSame(0, $this->summary('search=nobody')->json('totals.applications'));
    }

    public function test_search_treats_like_wildcards_literally(): void
    {
        $this->setUpTestData();

        // "%" and "_" would match every employee if they were passed through as wildcards.
        $this->assertSame(0, $this->summary('search=' . rawurlencode('%'))->json('totals.applications'));
        $this->assertSame(0, $this->summary('search=' . rawurlencode('_'))->json('totals.applications'));
        $this->assertSame(0, $this->summary('search=' . rawurlencode('a%a'))->json('totals.applications'));
        $this->assertSame(0, $this->summary('search=' . rawurlencode('!'))->json('totals.applications'));
    }

    public function test_search_normalises_blank_input_and_limits_words(): void
    {
        $this->setUpTestData();

        $blank = $this->summary('search=' . rawurlencode('   '))->assertOk();
        $this->assertNull($blank->json('filters.search'));
        $this->assertSame(10, $blank->json('totals.applications'));

        $many = $this->summary('search=' . rawurlencode('a b c d e f g h'))->assertOk();
        $this->assertSame('a b c d e', $many->json('filters.search'));
    }

    public function test_filters_combine_with_dates_and_employee_scope(): void
    {
        $seeded = $this->setUpTestData();
        // Outside the reporting period, so the date filter must still apply together with the others.
        $this->leave($seeded['ana'], $this->vacation, 'approved', 9, '2026-08-01');

        $mathApprovedMarch = $this->summary(
            "department_id={$this->math->department_id}&status=approved&start_date=2026-03-01&end_date=2026-03-31"
        )->assertOk();
        // Ana (2), Dan (5) and archived Eve (1); the August application is outside the period.
        $this->assertSame(3, $mathApprovedMarch->json('totals.applications'));
        $this->assertSame('8.000', $mathApprovedMarch->json('totals.approved_days'));
        $this->assertConsistent($mathApprovedMarch);

        // Excluding inactive/archived employees keeps the same other filters.
        $activeOnly = $this->summary(
            "department_id={$this->math->department_id}&status=approved&start_date=2026-03-01&end_date=2026-03-31&include_inactive=0"
        )->assertOk();
        $this->assertSame(1, $activeOnly->json('totals.applications'));
        $this->assertSame('2.000', $activeOnly->json('totals.approved_days'));
        $this->assertConsistent($activeOnly);

        $august = $this->summary("search=ana&start_date=2026-08-01&end_date=2026-08-31")->assertOk();
        $this->assertSame(1, $august->json('totals.applications'));

        $none = $this->summary("department_id={$this->science->department_id}&status=pending")->assertOk();
        $this->assertSame(0, $none->json('totals.applications'));
        $this->assertSame([], $none->json('summary'));
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->setUpTestData();

        $this->summary('status=cancelled')->assertStatus(422)->assertJsonValidationErrors('status');
        $this->summary('department_id=abc')->assertStatus(422)->assertJsonValidationErrors('department_id');
        $this->summary('department_id=999999')->assertStatus(422)->assertJsonValidationErrors('department_id');
        $this->summary('department_id=-1')->assertStatus(422)->assertJsonValidationErrors('department_id');
        $this->summary('department_id[]=1')->assertStatus(422)->assertJsonValidationErrors('department_id');
        $this->summary('leave_type_id=999999')->assertStatus(422)->assertJsonValidationErrors('leave_type_id');
        $this->summary('leave_type_id=abc')->assertStatus(422)->assertJsonValidationErrors('leave_type_id');
        $this->summary('search=' . str_repeat('a', 101))->assertStatus(422)->assertJsonValidationErrors('search');
        $this->summary('search[]=ana')->assertStatus(422)->assertJsonValidationErrors('search');
    }
}
