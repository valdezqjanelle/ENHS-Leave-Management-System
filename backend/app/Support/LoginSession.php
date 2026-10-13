<?php

namespace App\Support;

use App\Models\User;
use App\Models\AdminProfile;
use App\Models\EmployeeRecord;

class LoginSession
{
    public static function issue(User $user): array
    {
        abort_unless($user->email_verified_at, 403, 'Email verification is required.');
        $issued = $user->createToken('els_token');
        $issued->accessToken->forceFill(['last_activity_at' => now()])->save();
        AuditLogger::log('Login', "{$user->email} logged in", $user->user_id);
        return ['user' => self::userWithName($user), 'token' => $issued->plainTextToken];
    }

    public static function userWithName(User $user): array
    {
        $profile = $user->role === 'admin'
            ? AdminProfile::where('user_id', $user->user_id)->first()
            : EmployeeRecord::where('user_id', $user->user_id)->first();

        // Some Admin accounts also have an employee record.
        if ($user->role === 'admin' && (!$profile || !trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')))) {
            $profile = EmployeeRecord::where('user_id', $user->user_id)->first() ?? $profile;
        }

        $data = $user->toArray();
        $data['first_name'] = trim((string) ($profile?->first_name ?? ''));
        $data['last_name'] = trim((string) ($profile?->last_name ?? ''));
        $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
        return $data;
    }
}
