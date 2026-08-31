<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use App\Support\AuditLogger;

class LeaveBalanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN: VIEW ALL BALANCES
    |--------------------------------------------------------------------------
    */
public function index()
{
    $employees = EmployeeRecord::with('leaveBalance')->get();

    $balances = $employees->map(function ($employee) {

        return [
            'employee_id' => $employee->employee_id,

            'employee' => [
                'employee_id' => $employee->employee_id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
            ],

            'balance_id' =>
                $employee->leaveBalance?->balance_id,

            'vacation_earned' =>
                $employee->leaveBalance?->vacation_earned ?? 0,

            'sick_earned' =>
                $employee->leaveBalance?->sick_earned ?? 0,

            'vacation_balance' =>
                $employee->leaveBalance?->vacation_balance ?? 0,
            
            'service_credits' =>
                $employee->leaveBalance?->service_credits ?? 0,

            'sick_balance' =>
                $employee->leaveBalance?->sick_balance ?? 0,

            'used_leave' =>
                $employee->leaveBalance?->used_leave ?? 0,
        ];

    });

    return response()->json($balances);
}
    /*
    |--------------------------------------------------------------------------
    | ADMIN: VIEW SPECIFIC EMPLOYEE BALANCE
    |--------------------------------------------------------------------------
    */
    public function show($employee_id)
    {
        $employee = EmployeeRecord::find($employee_id);

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found.'
            ], 404);
        }

        $balance = LeaveBalance::with('employee')
            ->where('employee_id', $employee_id)
            ->first();

        if (!$balance) {
            return response()->json([
                'balance_id' => null,
                'employee_id' => $employee->employee_id,
                'vacation_earned' => 0,
                'sick_earned' => 0,
                'vacation_balance' => 0,
                'sick_balance' => 0,
                'service_credits' => 0,
                'used_leave' => 0,
                'last_updated' => null,
                'employee' => [
                    'employee_id' => $employee->employee_id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                ],
            ]);
        }

        return response()->json($balance);
    }

    
public function update(Request $request, $employee_id)
{
    $validated = $request->validate([
        'vacation_earned' => 'required|numeric|min:0',
        'sick_earned' => 'required|numeric|min:0',
        'vacation_balance' => 'required|numeric|min:0',
        'sick_balance' => 'required|numeric|min:0',
        'service_credits' => 'required|numeric|min:0',
    ]);

    $employee = EmployeeRecord::find($employee_id);

    if (!$employee) {
        return response()->json([
            'message' => 'Employee record not found.'
        ], 404);
    }

    $balance = LeaveBalance::where('employee_id', $employee_id)->first();
    $wasCreated = false;

    if (!$balance) {
        $balance = new LeaveBalance();
        $balance->employee_id = $employee->employee_id;
        $balance->used_leave = 0;
        $wasCreated = true;
    }

    $balance->vacation_earned = $validated['vacation_earned'];
    $balance->sick_earned = $validated['sick_earned'];
    $balance->vacation_balance = $validated['vacation_balance'];
    $balance->sick_balance = $validated['sick_balance'];
    $balance->service_credits = $validated['service_credits'];
    $balance->last_updated = now();
    $balance->save();

    AuditLogger::log(
        $wasCreated ? 'Leave balance created' : 'Leave balance updated',
        "Set leave balance for employee #{$employee_id} " .
        "(vacation: {$balance->vacation_balance}, sick: {$balance->sick_balance})"
    );

    return response()->json([
        'message' => $wasCreated
            ? 'Leave balance created successfully.'
            : 'Leave balance updated successfully.',
        'data' => $balance->fresh('employee')
    ], $wasCreated ? 201 : 200);
}

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE: VIEW OWN BALANCE
    |--------------------------------------------------------------------------
    */
    public function myBalance(Request $request)
{
    $employee = EmployeeRecord::where(
        'user_id',
        $request->user()->user_id
    )->first();

    if (!$employee) {
        return response()->json([
            'message' => 'Employee record not found'
        ], 404);
    }

    $balance = LeaveBalance::where(
        'employee_id',
        $employee->employee_id
    )->first();

    if (!$balance) {
        return response()->json([
            'vacation_balance' => 0,
            'sick_balance' => 0,
            'service_credits' => 0,
            'used_leave' => 0,
            'vacation_earned' => 0,
            'sick_earned' => 0,
            'last_updated' => null,
        ]);
    }

    return response()->json([
        'vacation_balance' => $balance->vacation_balance,
        'sick_balance' => $balance->sick_balance,
        'service_credits' => $balance->service_credits,
        'used_leave' => $balance->used_leave,
        'vacation_earned' => $balance->vacation_earned,
        'sick_earned' => $balance->sick_earned,
        'last_updated' => $balance->last_updated,
    ]);
}

public function destroy($employee_id)
{
    $employee = EmployeeRecord::find($employee_id);

    if (!$employee) {
        return response()->json([
            'message' => 'Employee record not found.'
        ], 404);
    }

    $balance = LeaveBalance::where('employee_id', $employee_id)->first();

    if (!$balance) {
        $balance = new LeaveBalance();
        $balance->employee_id = $employee->employee_id;
        $balance->used_leave = 0;
    }

    $balance->vacation_earned = 0;
    $balance->sick_earned = 0;
    $balance->vacation_balance = 0;
    $balance->sick_balance = 0;
    $balance->service_credits = 0;
    $balance->last_updated = now();
    $balance->save();

    AuditLogger::log(
        'Leave balance cleared',
        "Cleared leave balance for employee #{$employee_id}"
    );

    return response()->json([
        'message' => 'Leave balance cleared successfully.',
        'data' => $balance
    ], 200);
}
}
