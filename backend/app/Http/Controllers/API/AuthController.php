<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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

        $token = $user->createToken('els_token')->plainTextToken;

        AuditLogger::log('Login', "{$user->email} logged in", $user->user_id);

        return response()->json([
            'user' => $user,
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
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
