<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access checks around the CS Form 6 download and the final decision on an application.
 *
 * These do NOT test that the PDF is generated correctly. That needs a real leave application,
 * which needs the LeaveApplication / LeaveType models and the LeaveController code.
 */
class CsForm6AccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['email' => 'form-admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $this->staff = User::create(['email' => 'form-staff@example.test', 'password' => 'password', 'role' => 'employee']);
    }

    public function test_guest_cannot_download_a_cs_form_6(): void
    {
        $this->getJson('/api/leave-applications/1/pdf')->assertUnauthorized();
    }

    public function test_download_of_a_missing_application_is_not_found(): void
    {
        $this->actingAs($this->admin, 'sanctum')->getJson('/api/leave-applications/999999/pdf')->assertNotFound();
    }

    public function test_employee_cannot_record_a_final_decision(): void
    {
        $this->actingAs($this->staff, 'sanctum')->putJson('/api/leave-applications/1', ['status' => 'approved'])->assertForbidden();
    }

    public function test_employee_cannot_list_all_applications(): void
    {
        $this->actingAs($this->staff, 'sanctum')->getJson('/api/leave-applications')->assertForbidden();
    }
}
