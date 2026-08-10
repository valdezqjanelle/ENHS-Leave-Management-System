<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'role' => $user->role
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
