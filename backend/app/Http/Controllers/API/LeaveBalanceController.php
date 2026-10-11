<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

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

                'balance_id' => $employee->leaveBalance?->balance_id,

                'vacation_earned' =>
                    $employee->leaveBalance?->vacation_earned ?? 0,

                'sick_earned' =>
                    $employee->leaveBalance?->sick_earned ?? 0,

                'vacation_balance' =>
                    $employee->leaveBalance?->vacation_balance ?? 0,

                'sick_balance' =>
                    $employee->leaveBalance?->sick_balance ?? 0,

                'service_credits' =>
                    $employee->leaveBalance?->service_credits ?? 0,

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
                'message' => 'Employee record not found.',
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
            'vacation_balance' => 'required|numeric|min:0',
            'sick_balance' => 'required|numeric|min:0',
            'reason' => 'required|string|min:5|max:500',

            // Optional: supplied by Leave Credits synchronization.
            'vacation_earned' => 'sometimes|numeric|min:0',
            'sick_earned' => 'sometimes|numeric|min:0',
            'service_credits' => 'sometimes|numeric|min:0',
        ]);

        $employee = EmployeeRecord::find($employee_id);

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found.',
            ], 404);
        }

        [$balance, $wasCreated] = DB::transaction(function () use ($employee, $employee_id, $validated, $request) {
            EmployeeRecord::whereKey($employee_id)->lockForUpdate()->firstOrFail();
            $balance = LeaveBalance::withTrashed()->where('employee_id', $employee_id)->lockForUpdate()->first();
            if ($balance?->trashed()) $balance->restore();
            $wasCreated = !$balance;
            $balance ??= new LeaveBalance(['employee_id' => $employee->employee_id]);
            foreach (['vacation_earned', 'sick_earned', 'vacation_balance', 'sick_balance', 'service_credits', 'used_leave'] as $field) $balance->{$field} ??= 0;
            $beforeVacation = number_format((float) $balance->vacation_balance, 3, '.', '');
            $beforeSick = number_format((float) $balance->sick_balance, 3, '.', '');
            $balance->vacation_balance = number_format((float) $validated['vacation_balance'], 3, '.', '');
            $balance->sick_balance = number_format((float) $validated['sick_balance'], 3, '.', '');
            $balance->last_updated = now();
            $balance->save();
            DB::table('leave_balance_adjustments')->insert([
                'employee_id' => $employee_id, 'admin_user_id' => $request->user()->user_id,
                'vacation_before' => $beforeVacation, 'vacation_after' => $balance->vacation_balance,
                'sick_before' => $beforeSick, 'sick_after' => $balance->sick_balance,
                'reason' => $validated['reason'], 'created_at' => now(), 'updated_at' => now(),
            ]);
            return [$balance, $wasCreated];
        });

        AuditLogger::log(
            $wasCreated
                ? 'Leave balance created'
                : 'Leave balance updated',
            "Set leave balance for employee #{$employee_id} " .
            "(reason: {$validated['reason']}; vacation: {$balance->vacation_balance}, " .
            "sick: {$balance->sick_balance}, " .
            "service: {$balance->service_credits})"
        );

        return response()->json([
            'message' => $wasCreated
                ? 'Leave balance created successfully.'
                : 'Leave balance updated successfully.',
            'data' => $balance->fresh('employee'),
        ], $wasCreated ? 201 : 200);
    }

    public function history($employee_id)
    {
        abort_unless(EmployeeRecord::whereKey($employee_id)->exists(), 404, 'Employee record not found.');
        $history = DB::table('leave_balance_adjustments as a')
            ->join('users as u', 'u.user_id', '=', 'a.admin_user_id')
            ->where('a.employee_id', $employee_id)
            ->orderByDesc('a.created_at')
            ->select('a.id', 'a.vacation_before', 'a.vacation_after', 'a.sick_before', 'a.sick_after', 'a.reason', 'a.created_at', 'u.email as administrator')
            ->get();
        return response()->json(['data' => $history]);
    }

    public function myBalance(Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found',
            ], 404);
        }

        $balance = LeaveBalance::where(
            'employee_id',
            $employee->employee_id
        )->first();

        if (!$balance) {
            return response()->json([
                'vacation_earned' => 0,
                'sick_earned' => 0,
                'vacation_balance' => 0,
                'sick_balance' => 0,
                'service_credits' => 0,
                'used_leave' => 0,
                'last_updated' => null,
            ]);
        }

        return response()->json([
            'vacation_earned' => $balance->vacation_earned,
            'sick_earned' => $balance->sick_earned,
            'vacation_balance' => $balance->vacation_balance,
            'sick_balance' => $balance->sick_balance,
            'service_credits' => $balance->service_credits,
            'used_leave' => $balance->used_leave,
            'last_updated' => $balance->last_updated,
        ]);
    }

    public function destroy($employee_id)
    {
        $validated = request()->validate(['reason' => 'required|string|min:5|max:500']);
        $employee = EmployeeRecord::find($employee_id);

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found.',
            ], 404);
        }

        $balance = DB::transaction(function () use ($employee, $employee_id, $validated) {
            EmployeeRecord::whereKey($employee_id)->lockForUpdate()->firstOrFail();
            $balance = LeaveBalance::withTrashed()->where('employee_id', $employee_id)->lockForUpdate()->first();
            if ($balance?->trashed()) $balance->restore();
            $balance ??= new LeaveBalance(['employee_id' => $employee->employee_id]);
            foreach (['vacation_earned', 'sick_earned', 'vacation_balance', 'sick_balance', 'service_credits', 'used_leave'] as $field) $balance->{$field} ??= 0;
            $beforeVacation = number_format((float) $balance->vacation_balance, 3, '.', '');
            $beforeSick = number_format((float) $balance->sick_balance, 3, '.', '');
            $balance->vacation_balance = 0;
            $balance->sick_balance = 0;
            $balance->last_updated = now();
            $balance->save();
            DB::table('leave_balance_adjustments')->insert([
                'employee_id' => $employee_id, 'admin_user_id' => request()->user()->user_id,
                'vacation_before' => $beforeVacation, 'vacation_after' => '0.000', 'sick_before' => $beforeSick, 'sick_after' => '0.000',
                'reason' => $validated['reason'], 'created_at' => now(), 'updated_at' => now(),
            ]);
            return $balance;
        });

        AuditLogger::log(
            'Leave balance cleared',
            "Cleared vacation and sick balances for employee #{$employee_id}: {$validated['reason']}"
        );

        return response()->json([
            'message' => 'Leave balance cleared successfully.',
            'data' => $balance,
        ], 200);
    }
}
