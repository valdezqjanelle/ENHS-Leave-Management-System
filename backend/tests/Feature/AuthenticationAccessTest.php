<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EmployeeRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['email' => 'auth-admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $this->staff = User::create(['email' => 'auth-staff@example.test', 'password' => 'password', 'role' => 'employee']);
        $department = Department::create(['department_name' => 'Auth Test Department', 'level' => 'JHS']);
        EmployeeRecord::create([
            'user_id' => $this->staff->user_id, 'employee_code' => 'ENHS-AUTH-1', 'first_name' => 'Test',
            'last_name' => 'Employee', 'sex' => 'Female', 'employment_status' => 'active',
            'date_hired' => '2020-01-01', 'department_id' => $department->department_id,
        ]);
    }

    /** The response must be a client-side refusal (401, 403, 422 and so on), never a success or a crash. */
    private function assertRefused($response): void
    {
        $status = $response->getStatusCode();
        $this->assertTrue($status >= 400 && $status < 500, "Expected a 4xx refusal, got {$status}");
    }

    public function test_employee_can_log_in_and_receives_a_token(): void
    {
        $this->postJson('/api/login', ['email' => 'auth-staff@example.test', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['email', 'role']])
            ->assertJsonPath('user.email', 'auth-staff@example.test')
            ->assertJsonPath('user.role', 'employee');
    }

    public function test_admin_can_log_in_and_receives_the_admin_role(): void
    {
        $this->postJson('/api/login', ['email' => 'auth-admin@example.test', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_wrong_password_is_refused_without_a_token(): void
    {
        $response = $this->postJson('/api/login', ['email' => 'auth-staff@example.test', 'password' => 'wrong-password']);
        $this->assertRefused($response);
        $response->assertJsonMissingPath('token');
    }

    public function test_unknown_email_is_refused_without_a_token(): void
    {
        $response = $this->postJson('/api/login', ['email' => 'nobody@example.test', 'password' => 'password']);
        $this->assertRefused($response);
        $response->assertJsonMissingPath('token');
    }

    public function test_token_from_login_opens_a_protected_route(): void
    {
        $token = $this->postJson('/api/login', ['email' => 'auth-staff@example.test', 'password' => 'password'])
            ->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', 'auth-staff@example.test');
    }

    public function test_protected_route_requires_login(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_unverified_email_cannot_use_the_system(): void
    {
        // TestCase marks every new user as verified, so clear the date after creating the user.
        User::where('user_id', $this->staff->user_id)->update(['email_verified_at' => null]);

        $response = $this->actingAs($this->staff->fresh(), 'sanctum')->getJson('/api/me');
        $this->assertRefused($response);
    }

    public function test_employee_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/admin/dashboard')->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/audit-logs')->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/backups')->assertForbidden();
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/employees')->assertForbidden();
    }

    public function test_admin_cannot_use_employee_only_pages(): void
    {
        $this->actingAs($this->admin, 'sanctum')->getJson('/api/employee/dashboard')->assertForbidden();
    }

    public function test_logout_revokes_the_token(): void
    {
        $token = $this->postJson('/api/login', ['email' => 'auth-staff@example.test', 'password' => 'password'])
            ->assertOk()->json('token');

        $this->withToken($token)->postJson('/api/logout')->assertSuccessful();

        // Forget the user cached from the previous request so the token is checked again.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }
}
