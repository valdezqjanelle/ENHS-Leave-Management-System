<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;

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
        $balance = LeaveBalance::with('employee')
            ->where('employee_id', $employee_id)
            ->first();

        if (!$balance) {
            return response()->json([
                'message' => 'Leave balance not found'
            ], 404);
        }

        return response()->json($balance);
    }

    public function update(Request $request, $employee_id)
{
    $request->validate([
        'vacation_earned' => 'required|numeric|min:0',
        'sick_earned' => 'required|numeric|min:0',
        'vacation_balance' => 'required|numeric|min:0',
        'sick_balance' => 'required|numeric|min:0',
        'service_credits' => 'required|numeric|min:0',
    ]);

    $balance = LeaveBalance::updateOrCreate(
        [
            'employee_id' => $employee_id
        ],
        [
            'vacation_earned' => $request->vacation_earned,
            'sick_earned' => $request->sick_earned,
            'vacation_balance' => $request->vacation_balance,
            'sick_balance' => $request->sick_balance,
            'service_credits' => $request->service_credits,
            'last_updated' => now()
        ]
    );

    return response()->json([
        'message' => 'Leave balance saved successfully',
        'data' => $balance
    ]);
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
}
