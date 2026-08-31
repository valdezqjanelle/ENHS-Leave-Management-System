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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'sex' => 'required|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:50',
            'personal_email' => 'nullable|email|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
            'personnel_type' => 'required|in:Teaching,Non-Teaching,School Head',
            'employment_status' => 'required|in:active,inactive',
            'employment_category' => 'nullable|in:Permanent,Probationary,Contractual,Casual,Temporary,Contract of Service,Job Order',
            'date_hired' => 'required|date',
            'department_id' => 'required|exists:departments,department_id',
            'position_id' => 'required|exists:positions,id',
            'supervisor_id' => 'nullable|exists:employee_records,employee_id',
            'salary_step' => 'required|integer|min:1|max:8',
            'level' => 'required|in:JHS,SHS,Non-Teaching',
        ]);

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
            'extension_name' => $request->extension_name,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'civil_status' => $request->civil_status,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'personal_email' => $request->personal_email,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_number' => $request->emergency_contact_number,
            'personnel_type' => $request->personnel_type,
            'employment_status' => $request->employment_status,
            'employment_category' => $request->employment_category,
            'date_hired' => $request->date_hired,
            'department_id' => $request->department_id,
            'position_id' => $position->id,
            'supervisor_id' => $request->supervisor_id,
            'salary_step' => $request->salary_step,
            'salary' => $salary,
            'level' => $request->level,
        ]);

        AuditLogger::log(
            'Employee created',
            "Created employee {$employee->first_name} {$employee->last_name} ({$user->email})"
        );

        return response()->json([
            'message' => 'Employee created successfully',
            'email' => $user->email,
            'password' => $plainPassword,
            'employee' => $employee->load([
                'user',
                'position',
                'department',
                'supervisor'
            ])
        ]);
    }

    public function listDepartments(Request $request)
    {
        $query = \App\Models\Department::query()
            ->orderBy('department_name');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        return response()->json($query->get());
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

        $salary = SalarySchedule::where('salary_grade', $position->salary_grade)
            ->where('step', $request->salary_step)
            ->value('salary');

        return response()->json([
            'salary_grade' => $position->salary_grade,
            'step' => $request->salary_step,
            'salary' => $salary
        ]);
    }

    public function index()
    {
        $employees = EmployeeRecord::with([
            'user',
            'createdBy',
            'position',
            'department',
            'supervisor'
        ])
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
$employee = EmployeeRecord::findOrFail($id);


$request->validate([
    'email' => 'nullable|email|unique:users,email,' . $employee->user->user_id . ',user_id',
    'first_name' => 'required|string|max:255',
    'middle_name' => 'nullable|string|max:255',
    'last_name' => 'required|string|max:255',
    'extension_name' => 'nullable|string|max:50',
    'date_of_birth' => 'nullable|date',
    'sex' => 'required|string|max:50',
    'civil_status' => 'nullable|string|max:50',
    'nationality' => 'nullable|string|max:100',
    'address' => 'nullable|string',
    'contact_number' => 'nullable|string|max:50',
    'personal_email' => 'nullable|email|max:255',
    'emergency_contact_name' => 'nullable|string|max:255',
    'emergency_contact_number' => 'nullable|string|max:50',
    'personnel_type' => 'required|in:Teaching,Non-Teaching,School Head',
    'employment_status' => 'required|in:active,inactive',
    'employment_category' => 'nullable|in:Permanent,Probationary,Contractual,Casual,Temporary,Contract of Service,Job Order',
    'date_hired' => 'required|date',
    'department_id' => 'required|exists:departments,department_id',
    'position_id' => 'required|exists:positions,id',
    'supervisor_id' => 'nullable|exists:employee_records,employee_id',
    'salary_step' => 'required|integer|min:1|max:8',
    'level' => 'required|in:JHS,SHS,Non-Teaching',
]);

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

$employee->update([
    'first_name' => $request->first_name,
    'middle_name' => $request->middle_name,
    'last_name' => $request->last_name,
    'extension_name' => $request->extension_name,
    'date_of_birth' => $request->date_of_birth,
    'sex' => $request->sex,
    'civil_status' => $request->civil_status,
    'nationality' => $request->nationality,
    'address' => $request->address,
    'contact_number' => $request->contact_number,
    'personal_email' => $request->personal_email,
    'emergency_contact_name' => $request->emergency_contact_name,
    'emergency_contact_number' => $request->emergency_contact_number,
    'personnel_type' => $request->personnel_type,
    'employment_status' => $request->employment_status,
    'employment_category' => $request->employment_category,
    'date_hired' => $request->date_hired,
    'department_id' => $request->department_id,
    'level' => $request->level,
    'position_id' => $position->id,
    'supervisor_id' => $request->supervisor_id,
    'salary_step' => $request->salary_step,
    'salary' => $salary,
]);

if (
    $request->filled('email') &&
    $request->email !== $employee->user->email
) {
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
    'employee' => $employee->fresh()->load([
        'user',
        'position',
        'department',
        'supervisor'
    ])
]);


}


    public function myProfile(Request $request)
    {
        $employee = EmployeeRecord::with([
            'user',
            'position',
            'department',
            'supervisor'
        ])
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
            'extension_name' => $employee->extension_name,
            'date_of_birth' => $employee->date_of_birth,
            'sex' => $employee->sex,
            'civil_status' => $employee->civil_status,
            'nationality' => $employee->nationality,
            'address' => $employee->address,
            'contact_number' => $employee->contact_number,
            'personal_email' => $employee->personal_email,
            'emergency_contact_name' => $employee->emergency_contact_name,
            'emergency_contact_number' => $employee->emergency_contact_number,
            'personnel_type' => $employee->personnel_type,
            'employment_status' => $employee->employment_status,
            'employment_category' => $employee->employment_category,
            'date_hired' => $employee->date_hired,
            'years_of_service' => $employee->years_of_service,
            'department_id' => $employee->department_id,
            'department_name' => $employee->department?->department_name,
            'position_id' => $employee->position_id,
            'position' => $employee->position?->name,
            'supervisor_id' => $employee->supervisor_id,
            'supervisor' => $employee->supervisor
                ? $employee->supervisor->first_name . ' ' . $employee->supervisor->last_name
                : null,
            'level' => $employee->level,
            'salary_step' => $employee->salary_step,
            'salary' => $employee->salary,
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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'sex' => 'required|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:50',
            'personal_email' => 'nullable|email|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
        ]);

        $employee->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'extension_name' => $request->extension_name,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'civil_status' => $request->civil_status,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'personal_email' => $request->personal_email,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_number' => $request->emergency_contact_number,
        ]);

        AuditLogger::log(
            'Profile updated',
            "Updated profile for employee {$employee->first_name} {$employee->last_name}"
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'employee' => $employee->fresh()
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
            ->with([
                'user',
                'createdBy',
                'position',
                'department',
                'supervisor'
            ])
            ->latest('deleted_at')
            ->get();

        return response()->json($employees);
    }

    public function forceDestroy($id)
{
    $employee = EmployeeRecord::withTrashed()->findOrFail($id);

    $employeeName = $employee->first_name . ' ' . $employee->last_name;
    $userId = $employee->user_id;

    $employee->forceDelete();

    if ($userId) {
        User::where('user_id', $userId)->delete();
    }

    AuditLogger::log(
        'Employee permanently deleted',
        "Permanently deleted employee {$employeeName}"
    );

    return response()->json([
        'message' => 'Employee permanently deleted.'
    ]);
}
}
