<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminProfile;
use App\Models\Position;
use App\Models\SalarySchedule;

class AdminController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        $profile = AdminProfile::with(['position', 'department'])
            ->where('user_id', $user->user_id)
            ->first();

        return response()->json([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'role' => $user->role,
            'profile' => $profile
        ]);
    }

    public function positions()
    {
        return response()->json(
            Position::orderBy('name')->get([
                'id',
                'code',
                'name',
                'type',
                'salary_grade'
            ])
        );
    }

    public function departments(Request $request)
    {
        $query = \App\Models\Department::query()
            ->orderBy('department_name');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        return response()->json($query->get());
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'sex' => 'required|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'personal_email' => 'nullable|email|max:255',
            'level' => 'required|in:JHS,SHS,Non-Teaching',
            'position_id' => 'required|exists:positions,id',
            'salary_step' => 'required|integer|min:1|max:8',
            'department_id' => 'required|exists:departments,department_id',
            'contact_number' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        $profile = AdminProfile::where('user_id', $user->user_id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Admin profile not found'
            ], 404);
        }

        $position = Position::findOrFail($request->position_id);

        if (!$position->salary_grade) {
            return response()->json([
                'message' => 'This position does not have a salary grade.'
            ], 422);
        }

        $salary = SalarySchedule::where('salary_grade', $position->salary_grade)
            ->where('step', $request->salary_step)
            ->value('salary');

        if (!$salary) {
            return response()->json([
                'message' => 'No salary schedule found for Salary Grade '
                    . $position->salary_grade
                    . ' Step '
                    . $request->salary_step
            ], 422);
        }

        $profile->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'extension_name' => $request->extension_name,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'civil_status' => $request->civil_status,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'personal_email' => $request->personal_email,
            'level' => $request->level,
            'position_id' => $position->id,
            'salary_step' => $request->salary_step,
            'salary' => $salary,
            'department_id' => $request->department_id,
            'contact_number' => $request->contact_number,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_number' => $request->emergency_contact_number,
        ]);

        return response()->json([
            'message' => 'Admin profile updated successfully',
            'profile' => $profile->fresh()->load(['position', 'department']),
        ]);
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id'
        ]);

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
