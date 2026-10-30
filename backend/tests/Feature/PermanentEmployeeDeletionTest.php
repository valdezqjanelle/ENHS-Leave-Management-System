<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EmployeeRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PermanentEmployeeDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Deletion tests must use the supplied SQLite :memory: configuration.');
        }
    }
    private function employee(string $suffix): EmployeeRecord
    {
        $user = User::create(['email' => $suffix . '@example.test', 'password' => 'TestingPassword123!', 'role' => 'employee']);
        return EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => 'TEST-' . $suffix,
            'first_name' => 'Test',
            'last_name' => $suffix,
            'sex' => 'Female',
            'employment_status' => 'Permanent',
            'date_hired' => '2025-01-01'
        ]);
    }
    private function admin(): User
    {
        $user = User::create(['email' => 'admin@example.test', 'password' => 'TestingPassword123!', 'role' => 'admin']);
        // $user->forceFill(['email_verified_at' => now()])->save();
        $token = $user->createToken('test');
        $token->accessToken->forceFill(['last_activity_at' => now()])->save();
        $this->withToken($token->plainTextToken);
        Storage::fake('supabase');
        return $user;
    }
    private function adjustment(EmployeeRecord $employee, User $author): void
    {
        DB::table('leave_balance_adjustments')->insert([
            'employee_id' => $employee->employee_id,
            'admin_user_id' => $author->user_id,
            'vacation_before' => 15,
            'vacation_after' => 14,
            'sick_before' => 15,
            'sick_after' => 15,
            'reason' => 'Test adjustment',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    public function test_adjustment_history_no_longer_blocks_full_deletion(): void
    {
        $admin = $this->admin();
        $employee = $this->employee('remove');
        $other = $this->employee('keep');
        $this->adjustment($employee, $admin);
        $this->adjustment($other, $admin);
        $userId = $employee->user_id;
        $employee->user->createToken('old');
        $employee->delete();
        $this->deleteJson('/api/employees/' . $employee->employee_id . '/force')->assertOk()
            ->assertJsonPath('data.user_retained_for_history', false);
        $this->assertDatabaseMissing('employee_records', ['employee_id' => $employee->employee_id]);
        $this->assertDatabaseMissing('users', ['user_id' => $userId]);
        $this->assertDatabaseMissing('leave_balance_adjustments', ['employee_id' => $employee->employee_id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $userId, 'tokenable_type' => User::class]);
        $this->assertDatabaseHas('employee_records', ['employee_id' => $other->employee_id]);
        $this->assertDatabaseHas('leave_balance_adjustments', ['employee_id' => $other->employee_id]);
    }
    public function test_other_employees_history_survives_removal_of_its_author(): void
    {
        $this->admin();
        $employee = $this->employee('author');
        $other = $this->employee('other');
        $this->adjustment($other, $employee->user);
        $other->update(['supervisor_id' => $employee->employee_id, 'created_by' => $employee->user_id]);
        $userId = $employee->user_id;
        $employee->delete();
        $this->deleteJson('/api/employees/' . $employee->employee_id . '/force')->assertOk();
        $this->assertDatabaseMissing('users', ['user_id' => $userId]);
        $this->assertDatabaseHas('employee_records', ['employee_id' => $other->employee_id, 'supervisor_id' => null, 'created_by' => null]);
        $this->assertDatabaseHas('leave_balance_adjustments', [
            'employee_id' => $other->employee_id,
            'admin_user_id' => null,
            'admin_user_name' => 'Test author',
            'vacation_after' => 14
        ]);
    }
    public function test_active_employee_cannot_be_permanently_deleted(): void
    {
        $this->admin();
        $employee = $this->employee('active');
        $this->deleteJson('/api/employees/' . $employee->employee_id . '/force')->assertStatus(409);
        $this->assertDatabaseHas('employee_records', ['employee_id' => $employee->employee_id]);
        $this->assertDatabaseHas('users', ['user_id' => $employee->user_id]);
    }
    public function test_unknown_restricting_relationship_rolls_back_every_database_delete(): void
    {
        $admin = $this->admin();
        $employee = $this->employee('blocked');
        $this->adjustment($employee, $admin);
        Schema::create('test_external_reference', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('employee_id')->on('employee_records');
        });
        DB::table('test_external_reference')->insert(['employee_id' => $employee->employee_id]);
        $employee->delete();
        $this->deleteJson('/api/employees/' . $employee->employee_id . '/force')->assertStatus(409);
        $this->assertDatabaseHas('employee_records', ['employee_id' => $employee->employee_id]);
        $this->assertDatabaseHas('users', ['user_id' => $employee->user_id]);
        $this->assertDatabaseHas('leave_balance_adjustments', ['employee_id' => $employee->employee_id]);
        Schema::drop('test_external_reference');
    }
}
