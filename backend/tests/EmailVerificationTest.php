<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        // Never refresh a real agency database, even if configuration is cached.
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Run these tests only with the provided in-memory SQLite configuration.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'password_recovery.delivery' => 'brevo',
            'password_recovery.api_key' => 'test-only-key',
            'password_recovery.sender_email' => 'sender@example.test',
            'cache.default' => 'array'
        ]);
        Http::preventStrayRequests();
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => 'test'], 201)]);
    }

    private function account(string $role = 'employee'): User
    {
        return User::create(['email' => $role . '@example.test', 'password' => Hash::make('ExamplePassword123!'), 'role' => $role]);
    }
    private function challenge(User $user): array
    {
        return $this->postJson('/api/login', ['email' => $user->email, 'password' => 'ExamplePassword123!'])
            ->assertOk()->assertJsonPath('verification_required', true)->assertJsonMissingPath('token')->json();
    }
    private function latestCode(): string
    {
        $sent = Http::recorded();
        preg_match('/code is: (\d{6})/', $sent->last()[0]['textContent'], $matches);
        return $matches[1];
    }

    public function test_existing_and_new_accounts_verify_once_without_changing_passwords(): void
    {
        foreach (['employee', 'admin'] as $role) {
            $user = $this->account($role);
            $passwordHash = $user->password;
            $challenge = $this->challenge($user);
            $code = $this->latestCode();
            $verified = $this->postJson('/api/email-verification/verify', [
                'challenge_token' => $challenge['challenge_token'],
                'code' => $code,
            ])->assertOk()->assertJsonStructure(['token', 'user']);
            $this->assertNotNull($user->fresh()->email_verified_at);
            $this->assertSame($passwordHash, $user->fresh()->password);
            $this->withToken($verified->json('token'))->getJson('/api/me')->assertOk();
            $this->postJson('/api/email-verification/verify', [
                'challenge_token' => $challenge['challenge_token'],
                'code' => $code,
            ])->assertStatus(422);
            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'ExamplePassword123!'])
                ->assertOk()->assertJsonStructure(['token'])->assertJsonMissingPath('verification_required');
        }
        Http::assertSentCount(2);
    }

    public function test_old_unverified_tokens_cannot_access_the_system(): void
    {
        $user = $this->account();
        $issued = $user->createToken('old-session');
        $issued->accessToken->forceFill(['last_activity_at' => now()])->save();
        $this->withToken($issued->plainTextToken)->getJson('/api/me')
            ->assertUnauthorized()->assertJsonPath('code', 'EMAIL_VERIFICATION_REQUIRED');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_cooldown_and_old_code_invalidation(): void
    {
        $user = $this->account();
        $challenge = $this->challenge($user);
        $oldHash = DB::table('email_verification_codes')->where('user_id', $user->user_id)->value('otp_hash');
        $this->postJson('/api/email-verification/resend', ['challenge_token' => $challenge['challenge_token']])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->postJson('/api/email-verification/resend', ['challenge_token' => $challenge['challenge_token']])->assertOk();
        $newHash = DB::table('email_verification_codes')->where('user_id', $user->user_id)->value('otp_hash');
        $this->assertNotSame($oldHash, $newHash);
        Http::assertSentCount(2);
    }

    public function test_attempt_limit_cannot_be_reset_by_logging_in_again(): void
    {
        $user = $this->account();
        $challenge = $this->challenge($user);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/email-verification/verify', ['challenge_token' => $challenge['challenge_token'], 'code' => '000000'])->assertStatus(422);
        }
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'ExamplePassword123!'])->assertStatus(429);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_code_and_password_changes_do_not_verify_the_email(): void
    {
        $user = $this->account();
        $challenge = $this->challenge($user);
        $code = $this->latestCode();
        $this->travel(10)->minutes();
        $this->postJson('/api/email-verification/verify', ['challenge_token' => $challenge['challenge_token'], 'code' => $code])->assertStatus(422);
        $user->password = Hash::make('ChangedPassword123!');
        $user->save();
        $this->postJson('/api/email-verification/verify', ['challenge_token' => $challenge['challenge_token'], 'code' => $code])
            ->assertStatus(422)->assertJsonPath('code', 'VERIFICATION_SESSION_EXPIRED');
    }

    public function test_email_changes_require_verification_again(): void
    {
        $user = $this->account();
        // $user->forceFill(['email_verified_at' => now()])->save();
        $user->createToken('existing');
        $user->update(['email' => 'changed@example.test']);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(0, $user->tokens()->count());
        $this->challenge($user);
    }

    public function test_delivery_failure_does_not_issue_access_or_save_a_code(): void
    {
        $user = $this->account();
        Http::fake(['api.brevo.com/*' => Http::response([], 500)]);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'ExamplePassword123!'])->assertStatus(503);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->user_id]);
        $this->assertSame(0, $user->tokens()->count());
    }
}
