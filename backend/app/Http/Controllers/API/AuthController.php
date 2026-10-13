<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminProfile;
use App\Models\EmployeeRecord;
use Illuminate\Support\Facades\Hash;
use App\Support\AuditLogger;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $issuedToken = $user->createToken('els_token');
        $issuedToken->accessToken->forceFill(['last_activity_at' => now()])->save();
        $token = $issuedToken->plainTextToken;

        AuditLogger::log('Login', "{$user->email} logged in", $user->user_id);

        return response()->json([
            'user' => $this->userWithName($user),
            'token' => $token
        ]);
    }
    public function logout(Request $request)
    {
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        AuditLogger::log('Logout', "{$user->email} logged out", $user->user_id);

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // ME
    public function me(Request $request)
    {
        return response()->json($this->userWithName($request->user()));
    }

    private function userWithName(User $user): array
    {
        $profile = $user->role === 'admin'
            ? AdminProfile::where('user_id', $user->user_id)->first()
            : EmployeeRecord::where('user_id', $user->user_id)->first();

        // Some Admin accounts also have an employee record.
        if ($user->role === 'admin' && (!$profile || !trim(($profile->first_name ?? '').' '.($profile->last_name ?? '')))) {
            $profile = EmployeeRecord::where('user_id', $user->user_id)->first() ?? $profile;
        }

        $data = $user->toArray();
        $data['first_name'] = trim((string) ($profile?->first_name ?? ''));
        $data['last_name'] = trim((string) ($profile?->last_name ?? ''));
        $data['name'] = trim($data['first_name'].' '.$data['last_name']);
        return $data;
    }
}
