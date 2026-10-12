<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PasswordRecoveryController extends Controller
{
    private function user(string $email)
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        // Do not claim delivery when the configured driver cannot send email.
        if ((config('password_recovery.delivery') === 'brevo' && (!config('password_recovery.api_key') || !config('password_recovery.sender_email'))) || (config('password_recovery.delivery') !== 'brevo' && in_array(config('mail.default'), ['log', 'array', 'failover', 'roundrobin'], true))) {
            return response()->json(['message' => 'Password recovery email is not configured. Please contact the administrator.'], 503);
        }
        $user = $this->user($data['email']);
        $message = 'If this email is registered, a verification code will be sent. Check your inbox and spam folder.';
        if (!$user) {
            return response()->json(['message' => $message]);
        }
        try {
            DB::transaction(function () use ($user) {
                // Serializes resend, verification and reset for this account.
                $user = User::whereKey($user->user_id)->lockForUpdate()->firstOrFail();
                $row = DB::table('password_recovery_codes')->where('user_id', $user->user_id)->first();
                if ($row && Carbon::parse($row->last_sent_at)->gt(now()->subSeconds(60))) {
                    return;
                }
                if ($row && Carbon::parse($row->window_started_at)->gt(now()->subHour()) && $row->send_count >= 5) {
                    return;
                }
                $sameWindow = $row && Carbon::parse($row->window_started_at)->gt(now()->subHour());
                // Exhausted accounts must wait for the window to end; resend cannot bypass this.
                if ($sameWindow && $row->attempts >= 5) {
                    return;
                }
                $code = (string) random_int(100000, 999999);
                DB::table('password_recovery_codes')->updateOrInsert(['user_id' => $user->user_id], [
                    'otp_hash' => Hash::make($code),
                    'expires_at' => now()->addMinutes(10),
                    'last_sent_at' => now(),
                    'window_started_at' => $sameWindow ? $row->window_started_at : now(),
                    'send_count' => $sameWindow ? $row->send_count + 1 : 1,
                    'attempts' => $sameWindow ? $row->attempts : 0,
                    'reset_hash' => null,
                    'reset_expires_at' => null,
                    'password_hash_at_issue' => $user->password,
                ]);
                $text = "Your ENHS Leave System password recovery code is: {$code}\n\nThis code expires in 10 minutes. Enter it only in the system's Forgot Password screen. If you did not request this, ignore this email. Do not share this code.";
                if (config('password_recovery.delivery') === 'brevo') {
                    $response = Http::withHeaders(['api-key' => config('password_recovery.api_key')])
                        ->acceptJson()->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
                            'sender' => ['email' => config('password_recovery.sender_email'), 'name' => config('password_recovery.sender_name')],
                            'to' => [['email' => $user->email]],
                            'subject' => 'ENHS Leave System - Password Recovery Code',
                            'textContent' => $text,
                        ]);
                    if (!$response->successful()) {
                        // Do not log the provider response, request headers, key or OTP.
                        throw new \RuntimeException('Recovery email provider rejected the request (HTTP ' . $response->status() . ').');
                    }
                } else {
                    Mail::raw($text, function ($mail) use ($user) {
                        $mail->to($user->email)->subject('ENHS Leave System - Password Recovery Code');
                    });
                }
            });
        } catch (\Throwable $error) {
            \Illuminate\Support\Facades\Log::error('Password recovery delivery failed.', ['exception_type' => get_class($error)]);
            // Same response for all addresses, avoiding an account-existence signal.
        }
        return response()->json(['message' => $message]);
    }

    public function verifyCode(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'code' => 'required|digits:6']);
        $user = $this->user($data['email']);
        $token = $user ? DB::transaction(function () use ($user, $data) {
            $current = User::whereKey($user->user_id)->lockForUpdate()->firstOrFail();
            $row = DB::table('password_recovery_codes')->where('user_id', $user->user_id)->first();
            if (!$row || !$row->otp_hash || $row->attempts >= 5 || Carbon::parse($row->expires_at)->lte(now()) || !hash_equals($row->password_hash_at_issue, $current->password)) {
                return null;
            }
            if (!Hash::check($data['code'], $row->otp_hash)) {
                DB::table('password_recovery_codes')->where('user_id', $user->user_id)->increment('attempts');
                return null;
            }
            $token = Str::random(64);
            DB::table('password_recovery_codes')->where('user_id', $user->user_id)->update([
                'otp_hash' => null,
                'reset_hash' => hash('sha256', $token),
                'reset_expires_at' => now()->addMinutes(10),
            ]);
            return $token;
        }) : null;
        if (!$token) {
            return response()->json(['message' => 'Invalid or expired code. After too many attempts, wait one hour before requesting another code.'], 422);
        }
        return response()->json(['message' => 'Code verified.', 'reset_token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'reset_token' => 'required|string|size:64',
            'password' => 'required|string|min:12|max:128|confirmed',
        ]);
        $user = $this->user($data['email']);
        $done = $user ? DB::transaction(function () use ($user, $data) {
            $current = User::whereKey($user->user_id)->lockForUpdate()->firstOrFail();
            $row = DB::table('password_recovery_codes')->where('user_id', $user->user_id)->first();
            if (!$row || !$row->reset_hash || !$row->reset_expires_at || Carbon::parse($row->reset_expires_at)->lte(now()) || !hash_equals($row->reset_hash, hash('sha256', $data['reset_token'])) || !hash_equals($row->password_hash_at_issue, $current->password)) {
                return false;
            }
            $current->password = Hash::make($data['password']);
            $current->save();
            $current->tokens()->delete();
            if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                DB::table('sessions')->where('user_id', $current->user_id)->delete();
            }
            DB::table('password_recovery_codes')->where('user_id', $current->user_id)->delete();
            return true;
        }) : false;
        if (!$done) {
            return response()->json(['message' => 'Your reset session expired or is invalid. Please request a new code.'], 422);
        }
        return response()->json(['message' => 'Password updated successfully. Please sign in with your new password.']);
    }
}
