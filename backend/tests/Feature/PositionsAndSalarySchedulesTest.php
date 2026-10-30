<?php

namespace Tests\Feature;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionsAndSalarySchedulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['email' => 'pos-admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $this->staff = User::create(['email' => 'pos-staff@example.test', 'password' => 'password', 'role' => 'employee']);
    }

    public function test_guest_cannot_open_position_or_salary_pages(): void
    {
        $this->getJson('/api/admin/position-management')->assertUnauthorized();
        $this->getJson('/api/admin/salary-schedules')->assertUnauthorized();
    }

    public function test_employee_cannot_manage_positions(): void
    {
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/admin/position-management')->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->postJson('/api/admin/position-management', [
            'code' => 'TEST-POS-X', 'name' => 'Blocked Position', 'type' => 'Teaching', 'salary_grade' => 11, 'is_active' => true,
        ])->assertForbidden();
        $this->assertDatabaseMissing('positions', ['code' => 'TEST-POS-X']);
    }

    public function test_employee_cannot_manage_salary_schedules(): void
    {
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/admin/salary-schedules')->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->postJson('/api/admin/salary-schedules/1/activate', [])->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->deleteJson('/api/admin/salary-schedules/1')->assertForbidden();
    }

    public function test_admin_can_list_positions(): void
    {
        Position::create(['code' => 'TEST-POS-1', 'name' => 'Listed Position', 'type' => 'Teaching', 'salary_grade' => 11, 'is_active' => true]);

        $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/position-management')
            ->assertOk()
            ->assertSee('TEST-POS-1');
    }

    public function test_admin_can_add_a_position(): void
    {
        // If your controller requires other fields or different "type" values, adjust this payload.
        $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/position-management', [
            'code' => 'TEST-POS-2', 'name' => 'Added Position', 'type' => 'Teaching', 'salary_grade' => 12, 'is_active' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('positions', ['code' => 'TEST-POS-2', 'name' => 'Added Position']);
    }

    public function test_admin_can_edit_a_position(): void
    {
        $position = Position::create(['code' => 'TEST-POS-3', 'name' => 'Old Name', 'type' => 'Teaching', 'salary_grade' => 11, 'is_active' => true]);

        $this->actingAs($this->admin, 'sanctum')->putJson('/api/admin/position-management/' . $position->id, [
            'code' => 'TEST-POS-3', 'name' => 'New Name', 'type' => 'Teaching', 'salary_grade' => 11, 'is_active' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('positions', ['id' => $position->id, 'name' => 'New Name']);
    }

    public function test_admin_can_list_salary_schedules(): void
    {
        $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/salary-schedules')->assertOk();
    }
}
