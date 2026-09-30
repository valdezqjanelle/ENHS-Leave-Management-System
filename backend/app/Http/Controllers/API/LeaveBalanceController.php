<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use App\Support\AuditLogger;

class LeaveBalanceController extends Controller
{
 
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

       

            'vacation_balance' =>
                $employee->leaveBalance?->vacation_balance ?? 0,
            


            'sick_balance' =>
                $employee->leaveBalance?->sick_balance ?? 0,

            'used_leave' =>
                $employee->leaveBalance?->used_leave ?? 0,
        ];

    });

    return response()->json($balances);
}

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
              
                'vacation_balance' => 0,
                'sick_balance' => 0,
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

        'vacation_balance' => 'sometimes|required|numeric|min:0',
        'sick_balance' => 'sometimes|required|numeric|min:0',
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

 
    if (array_key_exists('vacation_balance', $validated)) {
        $balance->vacation_balance = $validated['vacation_balance'];
    }
    if (array_key_exists('sick_balance', $validated)) {
        $balance->sick_balance = $validated['sick_balance'];
    }

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
            'used_leave' => 0,
          
            'last_updated' => null,
        ]);
    }

    return response()->json([
        'vacation_balance' => $balance->vacation_balance,
        'sick_balance' => $balance->sick_balance,
        'used_leave' => $balance->used_leave,

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

 
    $balance->vacation_balance = 0;
    $balance->sick_balance = 0;
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
