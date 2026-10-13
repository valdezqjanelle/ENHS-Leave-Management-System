<?php
namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserEmailVerificationObserver
{
    public function creating(User $user): void
    {
        // New employee/admin accounts always start unverified.
        $user->email_verified_at = null;
    }
    public function updating(User $user): void
    {
        if ($user->isDirty('email')) $user->email_verified_at = null;
    }
    public function updated(User $user): void
    {
        if ($user->wasChanged('email')) {
            // Applies to employee and admin settings and admin employee edits.
            $user->tokens()->delete();
            DB::table('email_verification_codes')->where('user_id', $user->user_id)->delete();
            DB::table('password_recovery_codes')->where('user_id', $user->user_id)->delete();
        }
    }
}
