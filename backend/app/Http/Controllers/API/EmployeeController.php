<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Support\AuditLogger;

class EmployeeController extends Controller
{
    // CREATE EMPLOYEE (ADMIN ONLY)
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'department' => 'required|string',
            'position' => 'required|string'
        ]);

        $plainPassword = Str::random(8);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => 'employee'
        ]);

        $employee = EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'department' => $request->department,
            'position' => $request->position,
            'employee_category' => $request->employee_category,
            'salary' => $request->salary,
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
            'employee' => $employee
        ]);
    }

    public function index()
    {
        $employees = EmployeeRecord::with('user')->latest()->get();

        return response()->json($employees);
    }

    // UPDATE EMPLOYEE
public function update(Request $request, $id)
{
    $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'department' => 'required|string',
        'position' => 'required|string'
    ]);

    $employee = EmployeeRecord::findOrFail($id);

    // Update employee information
    $employee->update([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'sex' => $request->sex,
        'department' => $request->department,
        'position' => $request->position,
        'employee_category' => $request->employee_category,
        'salary' => $request->salary,
        'contact_number' => $request->contact_number,
        'employment_status' => $request->employment_status,
    ]);

    // Optional: update email if changed
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
        'employee' => $employee->load('user')
    ]);
}

   public function myProfile(Request $request)
{
    $employee = EmployeeRecord::with('user')
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
        'position' => $employee->position,
        'employee_category' => $employee->employee_category,

        'salary' => $employee->salary,
        'contact_number' => $employee->contact_number,
        'employment_status' => $employee->employment_status,
        'date_hired' => $employee->date_hired,

        'email' => $employee->user->email,
        'role' => $employee->user->role,
    ]);
}
// UPDATE OWN PROFILE
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
        'position' => 'required|string',
    ]);


    $employee->update([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'sex' => $request->sex,
        'department' => $request->department,
        'position' => $request->position,
    ]);


    return response()->json([
        'message' => 'Profile updated successfully',
        'employee' => $employee
    ]);
}



// UPDATE EMAIL
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



// UPDATE PASSWORD
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
            'message'=>'Current password incorrect'
        ],401);

    }


    $user->update([
        'password'=>Hash::make(
            $request->new_password
        )
    ]);


    return response()->json([
        'message'=>'Password updated successfully'
    ]);
}




// UPDATE PHONE
public function updatePhone(Request $request)
{
    $request->validate([
        'contact_number'=>'required|string'
    ]);


    $employee = EmployeeRecord::where(
        'user_id',
        $request->user()->user_id
    )->firstOrFail();


    $employee->update([
        'contact_number'=>$request->contact_number
    ]);


    return response()->json([
        'message'=>'Phone updated successfully'
    ]);
}
}
