<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmployeeRecord;
use App\Models\LeaveBalance;
use App\Models\User;
use Database\Seeders\WorkTimeConversionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveCreditsAndBalancesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private EmployeeRecord $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['email' => 'credit-admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $staff = User::create(['email' => 'credit-staff@example.test', 'password' => 'password', 'role' => 'employee']);
        $department = Department::create(['department_name' => 'Credit Test Department', 'level' => 'JHS']);
        $this->employee = EmployeeRecord::create([
            'user_id' => $staff->user_id, 'employee_code' => 'ENHS-CREDIT-1', 'first_name' => 'Test',
            'last_name' => 'Employee', 'sex' => 'Female', 'employment_status' => 'active',
            'date_hired' => '2020-01-01', 'department_id' => $department->department_id,
        ]);
        (new WorkTimeConversionSeeder())->run();
    }

    public function test_service_credit_uses_reference_and_posts_once(): void
    {
        $payload = ['employee_id' => $this->employee->employee_id, 'activity_name' => 'Weekend activity',
            'hours_rendered' => 1, 'equivalent_leave_days' => 0.125, 'credit_type' => 'Service'];
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/leave-credits', $payload)->assertOk();
        $this->assertSame('0.125', (string) $response->json('data.equivalent_leave_days'));
        $this->assertSame('Applied', $response->json('data.status'));
        $this->assertEquals(0.125, (float) LeaveBalance::where('employee_id', $this->employee->employee_id)->value('service_credits'));
        $this->actingAs($this->admin, 'sanctum')->postJson('/api/leave-credits/' . $response->json('data.credits_id') . '/apply', [])->assertStatus(400);
        $this->assertEquals(0.125, (float) LeaveBalance::where('employee_id', $this->employee->employee_id)->value('service_credits'));
    }

    public function test_service_credit_rejects_client_calculation_and_non_service_awards(): void
    {
        $this->actingAs($this->admin, 'sanctum')->postJson('/api/leave-credits', [
            'employee_id' => $this->employee->employee_id, 'activity_name' => 'Invalid calculation',
            'hours_rendered' => 1, 'equivalent_leave_days' => 1, 'credit_type' => 'Service',
        ])->assertUnprocessable();
        $this->assertDatabaseMissing('leave_credits', ['activity_name' => 'Invalid calculation']);

        $this->actingAs($this->admin, 'sanctum')->postJson('/api/leave-credits', [
            'employee_id' => $this->employee->employee_id, 'activity_name' => 'Manual award',
            'hours_rendered' => 0, 'equivalent_leave_days' => 1.5, 'credit_type' => 'Vacation',
        ])->assertUnprocessable();
        $this->assertDatabaseMissing('leave_credits', ['activity_name' => 'Manual award']);
    }

    public function test_credit_revocation_updates_balance_and_keeps_transaction_history(): void
    {
        $admin = $this->actingAs($this->admin, 'sanctum');
        $credit = $admin->postJson('/api/leave-credits', [
            'employee_id' => $this->employee->employee_id, 'activity_name' => 'Revocable activity',
            'hours_rendered' => 1, 'equivalent_leave_days' => 0.125, 'credit_type' => 'Service',
        ])->assertOk()->json('data');
        $admin->deleteJson('/api/leave-credits/' . $credit['credits_id'])->assertOk();
        $this->assertEquals(0, (float) LeaveBalance::where('employee_id', $this->employee->employee_id)->value('service_credits'));
        $this->assertSoftDeleted('leave_credits', ['credits_id' => $credit['credits_id']]);
        $this->assertDatabaseHas('leave_credits', ['credits_id' => $credit['credits_id'], 'revoked_by' => $this->admin->user_id, 'revoked_reason' => null]);
    }

    public function test_balance_adjustment_requires_reason_and_writes_admin_history(): void
    {
        LeaveBalance::create(['employee_id' => $this->employee->employee_id, 'vacation_balance' => 5, 'sick_balance' => 4]);
        $this->actingAs($this->admin, 'sanctum')->putJson('/api/leave-balances/' . $this->employee->employee_id, [
            'vacation_balance' => 6, 'sick_balance' => 3,
        ])->assertUnprocessable();
        $response = $this->actingAs($this->admin, 'sanctum')->putJson('/api/leave-balances/' . $this->employee->employee_id, [
            'vacation_balance' => 6, 'sick_balance' => 3, 'reason' => 'Corrected service record',
        ])->assertOk();
        $this->assertEquals(0, (float) $response->json('data.vacation_earned'));
        $this->assertDatabaseHas('leave_balance_adjustments', [
            'employee_id' => $this->employee->employee_id, 'admin_user_id' => $this->admin->user_id,
            'vacation_before' => 5, 'vacation_after' => 6, 'reason' => 'Corrected service record',
        ]);
        $this->actingAs($this->admin, 'sanctum')->getJson('/api/leave-balances/' . $this->employee->employee_id . '/history')
            ->assertOk()->assertJsonPath('data.0.administrator', 'credit-admin@example.test');
    }

    public function test_employee_cannot_edit_a_balance(): void
    {
        $staff = User::where('role', 'employee')->firstOrFail();
        $this->actingAs($staff, 'sanctum')->putJson('/api/leave-balances/' . $this->employee->employee_id, [
            'vacation_balance' => 1, 'sick_balance' => 1, 'reason' => 'Unauthorized edit',
        ])->assertForbidden();
    }
}
