<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SalarySchedule;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Support\AuditLogger;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'department' => 'required|string',
            'position_id' => 'required|exists:positions,id',
            'salary_step' => 'required|integer|min:1|max:8',
        ]);

        $position = Position::findOrFail($request->position_id);

        if (!$position->salary_grade) {
            return response()->json([
                'message' => 'This position does not have a salary grade.'
            ], 422);
        }

        $salary = SalarySchedule::where(
            'salary_grade',
            $position->salary_grade
        )
            ->where(
                'step',
                $request->salary_step
            )
            ->value('salary');

        if (!$salary) {
            return response()->json([
                'message' => 'No salary schedule found for Salary Grade '
                    . $position->salary_grade
                    . ' Step '
                    . $request->salary_step
            ], 422);
        }

        $plainPassword = Str::random(8);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => 'employee'
        ]);

        $employee = EmployeeRecord::create([
            'user_id' => $user->user_id,
            'created_by' => auth()->id(),
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'department' => $request->department,
            'position_id' => $position->id,
            'employee_category' => $request->employee_category,
            'salary_step' => $request->salary_step,
            'salary' => $salary,
            'contact_number' => $request->contact_number,
            'employment_status' => $request->employment_status ?? 'active',
            'date_hired' => now(),
        ]);

        AuditLogger::log(
            'Employee created',
            "Created employee {$employee->first_name} {$employee->last_name} ({$user->email})"
        );

        return response()->json([
            'message' => 'Employee created successfully',
            'email' => $user->email,
            'password' => $plainPassword,
            'employee' => $employee->load('position')
        ]);
    }

    public function salaryInfo(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'salary_step' => 'required|integer|min:1|max:8',
        ]);

        $position = Position::findOrFail($request->position_id);

        if (!$position->salary_grade) {
            return response()->json([
                'salary_grade' => null,
                'salary' => null,
                'message' => 'This position has no salary grade.'
            ]);
        }

        $salary = SalarySchedule::where(
            'salary_grade',
            $position->salary_grade
        )
            ->where(
                'step',
                $request->salary_step
            )
            ->value('salary');

        return response()->json([
            'salary_grade' => $position->salary_grade,
            'step' => $request->salary_step,
            'salary' => $salary
        ]);
    }

    public function index()
    {
        $employees = EmployeeRecord::with(['user', 'createdBy', 'position'])
            ->latest()
            ->get();

        return response()->json($employees);
    }

    public function listPositions()
    {
        return response()->json(
            Position::orderBy('name')->get()
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'department' => 'required|string',
            'position_id' => 'required|exists:positions,id',
            'salary_step' => 'required|integer|min:1|max:8',
        ]);

        $employee = EmployeeRecord::findOrFail($id);
        $position = Position::findOrFail($request->position_id);

        if (!$position->salary_grade) {
            return response()->json([
                'message' => 'This position does not have a salary grade.'
            ], 422);
        }

        $salary = SalarySchedule::where(
            'salary_grade',
            $position->salary_grade
        )
            ->where(
                'step',
                $request->salary_step
            )
            ->value('salary');

        if (!$salary) {
            return response()->json([
                'message' => 'No salary schedule found for Salary Grade '
                    . $position->salary_grade
                    . ' Step '
                    . $request->salary_step
            ], 422);
        }

        $employee->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'department' => $request->department,
            'position_id' => $position->id,
            'employee_category' => $request->employee_category,
            'salary_step' => $request->salary_step,
            'salary' => $salary,
            'contact_number' => $request->contact_number,
            'employment_status' => $request->employment_status,
        ]);

        if (
            $request->filled('email') &&
            $request->email !== $employee->user->email
        ) {
            $request->validate([
                'email' => 'email|unique:users,email,' . $employee->user->user_id . ',user_id'
            ]);

            $employee->user->update([
                'email' => $request->email
            ]);
        }

        AuditLogger::log(
            'Employee updated',
            "Updated employee {$employee->first_name} {$employee->last_name}"
        );

        return response()->json([
            'message' => 'Employee updated successfully.',
            'employee' => $employee->fresh()->load(['user', 'position'])
        ]);
    }

    public function myProfile(Request $request)
    {
        $employee = EmployeeRecord::with(['user', 'position'])
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found.'
            ], 404);
        }

        return response()->json([
            'employee_id' => $employee->employee_id,
            'employee_code' => $employee->employee_code,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'last_name' => $employee->last_name,
            'sex' => $employee->sex,
            'department' => $employee->department,
            'position_id' => $employee->position_id,
            'position' => $employee->position->name ?? null,
            'employee_category' => $employee->employee_category,
            'salary_step' => $employee->salary_step,
            'salary' => $employee->salary,
            'contact_number' => $employee->contact_number,
            'employment_status' => $employee->employment_status,
            'date_hired' => $employee->date_hired,
            'email' => $employee->user->email,
            'role' => $employee->user->role,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->firstOrFail();

        $request->validate([
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'sex' => 'required|string',
            'department' => 'required|string',
            'position_id' => 'required|exists:positions,id',
        ]);

        $employee->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'department' => $request->department,
            'position_id' => $request->position_id,
        ]);

        AuditLogger::log(
            'Profile updated',
            "Updated profile for employee {$employee->first_name} {$employee->last_name}"
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'employee' => $employee
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

    public function updatePhone(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|string'
        ]);

        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->firstOrFail();

        $employee->update([
            'contact_number' => $request->contact_number
        ]);

        return response()->json([
            'message' => 'Phone updated successfully'
        ]);
    }

    public function destroy($id)
{
    $employee = EmployeeRecord::findOrFail($id);

    $employee->delete();

    AuditLogger::log(
        'Employee deleted',
        "Soft deleted employee {$employee->first_name} {$employee->last_name}"
    );

    return response()->json([
        'message' => 'Employee deleted successfully.'
    ]);
}

public function restore($id)
{
    $employee = EmployeeRecord::withTrashed()->findOrFail($id);

    $employee->restore();

    AuditLogger::log(
        'Employee restored',
        "Restored employee {$employee->first_name} {$employee->last_name}"
    );

    return response()->json([
        'message' => 'Employee restored successfully.'
    ]);
}

public function deleted()
{
    $employees = EmployeeRecord::onlyTrashed()
        ->with(['user', 'createdBy', 'position'])
        ->latest('deleted_at')
        ->get();

    return response()->json($employees);
}
}