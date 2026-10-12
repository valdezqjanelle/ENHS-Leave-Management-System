<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function account(): User
    {
        config(['password_recovery.delivery' => 'brevo', 'password_recovery.api_key' => 'test-only', 'password_recovery.sender_email' => 'sender@example.test']);
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => 'test'], 201)]);
        return User::create(['email' => 'employee@example.test', 'password' => Hash::make('original-password'), 'role' => 'employee']);
    }
    private function code(): string
    {
        $code = '';
        Http::assertSent(function ($request) use (&$code) {
            preg_match('/code is: ([0-9]{6})/', $request['textContent'], $matches);
            $code = $matches[1] ?? '';
            return $code !== '';
        });
        return $code;
    }
    public function test_reset_requires_verified_code_and_revokes_tokens(): void
    {
        $user = $this->account();
        $user->createToken('existing');
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        $code = $this->code();
        $this->assertNotSame($code, DB::table('password_recovery_codes')->value('otp_hash'));
        $verified = $this->postJson('/api/forgot-password/verify', ['email' => $user->email, 'code' => $code])->assertOk();
        $this->postJson('/api/forgot-password/verify', ['email' => $user->email, 'code' => $code])->assertStatus(422);
        $payload = ['email' => $user->email, 'reset_token' => $verified->json('reset_token'), 'password' => 'my-new-long-password', 'password_confirmation' => 'my-new-long-password'];
        $this->postJson('/api/reset-password', $payload)->assertOk();
        $this->assertTrue(Hash::check($payload['password'], $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
        $this->postJson('/api/reset-password', $payload)->assertStatus(422);
    }
    public function test_expired_code_is_rejected(): void
    {
        $user = $this->account();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        $code = $this->code();
        $this->travel(11)->minutes();
        $this->postJson('/api/forgot-password/verify', ['email' => $user->email, 'code' => $code])->assertStatus(422);
    }
    public function test_wrong_attempts_cannot_be_bypassed_by_resending(): void
    {
        $user = $this->account();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-password/verify', ['email' => $user->email, 'code' => '000000'])->assertStatus(422);
        }
        $this->travel(61)->seconds();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        Http::assertSentCount(1);
    }
    public function test_unknown_email_does_not_send_email(): void
    {
        $this->account();
        $this->postJson('/api/forgot-password', ['email' => 'missing@example.test'])->assertOk();
        Http::assertNothingSent();
    }
}
