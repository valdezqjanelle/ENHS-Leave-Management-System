<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovingAuthoritiesTest extends TestCase
{
    use RefreshDatabase;

    private const OFFICER_URL = '/api/admin/authorized-officer';

    private const SUPERINTENDENT_URL = '/api/admin/school-division-superintendent';

    private function admin(): User
    {
        return User::create([
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    public function test_officer_and_superintendent_load_blank_then_save_independently(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')->getJson(self::OFFICER_URL)
            ->assertOk()->assertExactJson(['name' => '']);
        $this->actingAs($admin, 'sanctum')->getJson(self::SUPERINTENDENT_URL)
            ->assertOk()->assertExactJson(['name' => '']);

        // Saving the officer (input is trimmed and whitespace collapsed)...
        $this->actingAs($admin, 'sanctum')
            ->putJson(self::OFFICER_URL, ['name' => '  Maria   L. Santos  '])
            ->assertOk()
            ->assertJsonPath('data.name', 'Maria L. Santos');

        // ...does not touch the superintendent.
        $this->actingAs($admin, 'sanctum')->getJson(self::OFFICER_URL)
            ->assertOk()->assertExactJson(['name' => 'Maria L. Santos']);
        $this->actingAs($admin, 'sanctum')->getJson(self::SUPERINTENDENT_URL)
            ->assertOk()->assertExactJson(['name' => '']);

        $this->actingAs($admin, 'sanctum')
            ->putJson(self::SUPERINTENDENT_URL, ['name' => 'Dr. Juan P. Dela Cruz, CESO V'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Dr. Juan P. Dela Cruz, CESO V');

        $this->actingAs($admin, 'sanctum')->getJson(self::OFFICER_URL)
            ->assertOk()->assertExactJson(['name' => 'Maria L. Santos']);

        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'authorized_officer_name',
            'setting_value' => 'Maria L. Santos',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => 'school_division_superintendent_name',
            'setting_value' => 'Dr. Juan P. Dela Cruz, CESO V',
        ]);

        // The School Principal's authorized_signatories record is never created or changed.
        $this->assertDatabaseCount('authorized_signatories', 0);

        // Saving again updates the same row instead of adding a duplicate key.
        $this->actingAs($admin, 'sanctum')
            ->putJson(self::OFFICER_URL, ['name' => 'Ana B. Reyes'])
            ->assertOk();

        $this->assertSame(
            1,
            SystemSetting::where('setting_key', 'authorized_officer_name')->count()
        );
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->user_id,
            'action' => '7.A Authorized Officer updated',
        ]);
    }

    public function test_name_is_validated(): void
    {
        $admin = $this->admin();

        foreach ([self::OFFICER_URL, self::SUPERINTENDENT_URL] as $url) {
            $this->actingAs($admin, 'sanctum')->putJson($url, [])
                ->assertStatus(422)->assertJsonValidationErrors('name');

            $this->actingAs($admin, 'sanctum')->putJson($url, ['name' => '   '])
                ->assertStatus(422)->assertJsonValidationErrors('name');

            $this->actingAs($admin, 'sanctum')->putJson($url, ['name' => str_repeat('A', 256)])
                ->assertStatus(422)->assertJsonValidationErrors('name');

            $this->actingAs($admin, 'sanctum')->putJson($url, ['name' => "Bad\x07Name"])
                ->assertStatus(422)->assertJsonValidationErrors('name');
        }

        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_only_administrators_can_read_or_change_the_names(): void
    {
        $employee = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);

        // Guests first: once actingAs() is used it stays authenticated for the rest of the test.
        foreach ([self::OFFICER_URL, self::SUPERINTENDENT_URL] as $url) {
            $this->getJson($url)->assertUnauthorized();
            $this->putJson($url, ['name' => 'Someone'])->assertUnauthorized();
        }

        foreach ([self::OFFICER_URL, self::SUPERINTENDENT_URL] as $url) {
            $this->actingAs($employee, 'sanctum')->getJson($url)->assertForbidden();
            $this->actingAs($employee, 'sanctum')
                ->putJson($url, ['name' => 'Someone'])->assertForbidden();
        }

        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_endpoints_do_not_expose_other_system_settings(): void
    {
        SystemSetting::create(['setting_key' => 'school_name', 'setting_value' => 'ENHS']);

        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')->getJson(self::OFFICER_URL)
            ->assertOk()->assertExactJson(['name' => '']);
        $this->actingAs($admin, 'sanctum')
            ->putJson(self::OFFICER_URL, ['name' => 'Maria L. Santos'])
            ->assertOk()
            ->assertExactJson([
                'message' => 'Authorized officer name updated successfully.',
                'data' => ['name' => 'Maria L. Santos'],
            ]);
    }
}
