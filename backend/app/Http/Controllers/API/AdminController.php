<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminProfile;

class AdminController extends Controller
{

    public function profile(Request $request)
    {
        $user = $request->user();

        $profile = AdminProfile::where(
            'user_id',
            $user->user_id
        )->first();

        return response()->json([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'role' => $user->role,

            'profile' => $profile
        ]);
    }


 public function updateProfile(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'sex' => 'required|string|max:50',
        'position' => 'required|string|max:255',
        'department' => 'required|string|max:255',
        'contact_number' => 'nullable|string|max:20',
    ]);

    $user = $request->user();

    $profile = $user->adminProfile;

    if (!$profile) {
        return response()->json([
            'message' => 'Admin profile not found'
        ], 404);
    }

    $profile->update([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'sex' => $request->sex,
        'position' => $request->position,
        'department' => $request->department,
        'contact_number' => $request->contact_number,
    ]);

    return response()->json([
        'message' => 'Admin profile updated successfully',
        'profile' => $profile
    ]);
}


    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        $user = $request->user();

        $user->update([
            'email' => $request->email
        ]);

        return response()->json([
            'message' => 'Email updated successfully'
        ]);
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);

        $user = $request->user();

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {
            return response()->json([
                'message' => 'Current password incorrect'
            ], 401);
        }

        $user->update([
            'password' => Hash::make(
                $request->new_password
            )
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }
}