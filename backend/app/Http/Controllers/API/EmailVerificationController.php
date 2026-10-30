<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\LoginSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    // Called only after AuthController validates the current password.
    // The random challenge authorizes ONLY verification; it is not an API token.
    public function begin(User $authenticated)
    {
        try {
            return DB::transaction(function () use ($authenticated) {
                $user = User::whereKey($authenticated->user_id)->lockForUpdate()->firstOrFail();
                if (
                    !hash_equals($authenticated->password, $user->password)
                    || $authenticated->email !== $user->email
                ) {
                    return response()->json(['message' => 'Your credentials changed. Please sign in again.'], 401);
                }
                if ($user->email_verified_at) return response()->json(LoginSession::issue($user));

                $row = DB::table('email_verification_codes')->where('user_id', $user->user_id)->first();
                $sameWindow = $row && Carbon::parse($row->window_started_at)->gt(now()->subHour());
                if ($sameWindow && $row->attempts >= 5) return $this->limited($row);

                $challenge = Str::random(64);
                $sameIdentity = $row && $row->email === $user->email
                    && hash_equals($row->password_hash_at_issue, $user->password);
                $canReuse = $sameIdentity && $row->otp_hash && $row->expires_at
                    && Carbon::parse($row->expires_at)->gt(now());
                $cooldown = $row && $row->last_sent_at
                    && Carbon::parse($row->last_sent_at)->gt(now()->subSeconds(60));
                if (!$canReuse && $sameWindow && $row->send_count >= 5) return $this->limited($row);
                if (!$canReuse && $cooldown) return $this->cooldown($row);

                DB::table('email_verification_codes')->updateOrInsert(['user_id' => $user->user_id], [
                    'email' => $user->email,
                    'password_hash_at_issue' => $user->password,
                    'challenge_hash' => hash('sha256', $challenge),
                    'challenge_expires_at' => now()->addMinutes(20),
                    'otp_hash' => $canReuse ? $row->otp_hash : null,
                    'expires_at' => $canReuse ? $row->expires_at : null,
                    'last_sent_at' => $row?->last_sent_at,
                    'window_started_at' => $sameWindow ? $row->window_started_at : now(),
                    'send_count' => $sameWindow ? $row->send_count : 0,
                    'attempts' => $sameWindow ? $row->attempts : 0,
                ]);
                if (!$canReuse) $this->sendOtp($user);
                $row = DB::table('email_verification_codes')->where('user_id', $user->user_id)->first();
                return response()->json([
                    'verification_required' => true,
                    'challenge_token' => $challenge,
                    'masked_email' => $this->maskEmail($user->email),
                    'challenge_expires_at' => Carbon::parse($row->challenge_expires_at)->toIso8601String(),
                    'resend_after' => $this->resendAfter($row),
                    'message' => $canReuse
                        ? 'Enter the most recent verification code sent to your email.'
                        : 'A verification code has been sent to your email. Check your inbox and spam folder.',
                ]);
            });
        } catch (\Throwable $error) {
            Log::error('Email verification delivery failed.', ['exception_type' => get_class($error)]);
            return response()->json(['message' => 'We could not send the verification email. Please try again later or contact the administrator.'], 503);
        }
    }

    public function resend(Request $request)
    {
        $data = $request->validate(['challenge_token' => 'required|string|size:64']);
        try {
            return $this->withChallenge($data['challenge_token'], function ($user, $row) {
                if ($row->attempts >= 5 || $row->send_count >= 5) return $this->limited($row);
                if ($this->resendAfter($row) > 0) return $this->cooldown($row);
                $this->sendOtp($user);
                DB::table('email_verification_codes')->where('user_id', $user->user_id)
                    ->update(['challenge_expires_at' => now()->addMinutes(20)]);
                return response()->json([
                    'message' => 'A new code has been sent. Use the latest email.',
                    'resend_after' => 60,
                    'challenge_expires_at' => now()->addMinutes(20)->toIso8601String()
                ]);
            });
        } catch (\Throwable $error) {
            Log::error('Email verification resend failed.', ['exception_type' => get_class($error)]);
            return response()->json(['message' => 'We could not send the verification email. Please try again later.'], 503);
        }
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'challenge_token' => 'required|string|size:64',
            'code' => 'required|digits:6',
        ]);
        return $this->withChallenge($data['challenge_token'], function ($user, $row) use ($data) {
            if ($row->attempts >= 5) return $this->limited($row);
            if (!$row->otp_hash || !$row->expires_at || Carbon::parse($row->expires_at)->lte(now())) {
                return response()->json(['message' => 'This code has expired. Please request a new code.'], 422);
            }
            if (!Hash::check($data['code'], $row->otp_hash)) {
                DB::table('email_verification_codes')->where('user_id', $user->user_id)->increment('attempts');
                return response()->json([
                    'message' => $row->attempts + 1 >= 5
                        ? 'Too many incorrect attempts. Please wait one hour before trying again.'
                        : 'Incorrect code. Please check the latest verification email.',
                ], 422);
            }
            // $user->forceFill(['email_verified_at' => now()])->save();
            DB::table('email_verification_codes')->where('user_id', $user->user_id)->delete();
            // Remove any pre-verification sessions, then issue a fresh active session.
            $user->tokens()->delete();
            AuditLogger::log('Email verified', "Verified email for user #{$user->user_id}", $user->user_id);
            return response()->json(['message' => 'Email verified successfully.'] + LoginSession::issue($user));
        });
    }

    private function withChallenge(string $challenge, callable $action)
    {
        $hash = hash('sha256', $challenge);
        $found = DB::table('email_verification_codes')->where('challenge_hash', $hash)->first();
        if (!$found) return $this->invalidChallenge();
        return DB::transaction(function () use ($found, $hash, $action) {
            $user = User::whereKey($found->user_id)->lockForUpdate()->first();
            $row = DB::table('email_verification_codes')->where('user_id', $found->user_id)->first();
            if (
                !$user || !$row || !hash_equals($row->challenge_hash, $hash)
                || Carbon::parse($row->challenge_expires_at)->lte(now())
                || $row->email !== $user->email
                || !hash_equals($row->password_hash_at_issue, $user->password)
                || $user->email_verified_at
            ) return $this->invalidChallenge();
            if (Carbon::parse($row->window_started_at)->lte(now()->subHour())) {
                DB::table('email_verification_codes')->where('user_id', $user->user_id)->update([
                    'window_started_at' => now(),
                    'attempts' => 0,
                    'send_count' => 0,
                ]);
                $row = DB::table('email_verification_codes')->where('user_id', $user->user_id)->first();
            }
            return $action($user, $row);
        });
    }

    private function sendOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        $text = "Your ENHS Leave System email verification code is: {$code}\n\nThis code expires in 10 minutes. Enter it only on the Verify Your Email page. This confirms that you can access the email registered to your account. Do not share this code. If you did not sign in, ignore this email.";
        // Reuse the already working Brevo environment variables and verified sender.
        if (config('password_recovery.delivery') === 'brevo') {
            if (!config('password_recovery.api_key') || !config('password_recovery.sender_email')) {
                throw new \RuntimeException('Email verification delivery is not configured.');
            }
            $response = Http::withHeaders(['api-key' => config('password_recovery.api_key')])
                ->acceptJson()->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => ['email' => config('password_recovery.sender_email'), 'name' => config('password_recovery.sender_name')],
                    'to' => [['email' => $user->email]],
                    'subject' => 'ENHS Leave System - Verify Your Email',
                    'textContent' => $text,
                ]);
            if (!$response->successful()) throw new \RuntimeException('Email provider rejected verification delivery.');
        } else {
            if (in_array(config('mail.default'), ['log', 'array', 'failover', 'roundrobin'], true)) {
                throw new \RuntimeException('Email verification needs a working email transport.');
            }
            Mail::raw($text, fn($mail) => $mail->to($user->email)->subject('ENHS Leave System - Verify Your Email'));
        }
        DB::table('email_verification_codes')->where('user_id', $user->user_id)->update([
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'last_sent_at' => now(),
        ]);
        DB::table('email_verification_codes')->where('user_id', $user->user_id)->increment('send_count');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        return substr($local, 0, 1) . '***@' . $domain;
    }
    private function resendAfter($row): int
    {
        if (!$row->last_sent_at) return 0;
        return max(0, (int) ceil(now()->diffInSeconds(Carbon::parse($row->last_sent_at)->addSeconds(60), false)));
    }
    private function cooldown($row)
    {
        return response()->json(['message' => 'Please wait before requesting another code.', 'resend_after' => $this->resendAfter($row)], 429);
    }
    private function limited($row)
    {
        return response()->json([
            'message' => 'The verification limit has been reached. Please wait until the hourly limit resets and sign in again.',
            'retry_after' => max(1, (int) ceil(now()->diffInSeconds(Carbon::parse($row->window_started_at)->addHour(), false))),
        ], 429);
    }
    private function invalidChallenge()
    {
        return response()->json(['message' => 'Your verification session expired or changed. Please sign in again.', 'code' => 'VERIFICATION_SESSION_EXPIRED'], 422);
    }
}
