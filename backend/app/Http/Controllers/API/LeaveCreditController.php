<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveCredit;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use Carbon\Carbon;
use App\Support\AuditLogger;

class LeaveCreditController extends Controller
{
    // ADD CREDIT
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'activity_name' => 'required|string',
            'hours_rendered' => 'required|numeric',
            'equivalent_leave_days' => 'required|numeric',
            'credit_type' => 'required|in:Vacation,Sick,Service Credits',
          
        ]);

        $credit = LeaveCredit::create([
            'employee_id' => $request->employee_id,
            'activity_name' => $request->activity_name,
            'hours_rendered' => $request->hours_rendered,
            'equivalent_leave_days' => $request->equivalent_leave_days,
            'credit_type' => $request->credit_type,
            'status' =>'Pending',
            'date_recorded' => Carbon::now(),
            'recorded_by' => auth()->user()->user_id,
        ]);

        // OPTIONAL: auto update leave balance (we will refine later)


        AuditLogger::log(
            'Leave credit added',
            "Added {$credit->equivalent_leave_days} {$credit->credit_type} credit day(s) for employee #{$credit->employee_id} ({$credit->activity_name})"
        );

        return response()->json([
            'message' => 'Leave credit added successfully',
            'data' => $credit
        ]);
    }

public function index()
{
    $credits = LeaveCredit::with('employee')
        ->orderBy('credits_id', 'desc')
        ->get();

    return response()->json($credits);
}
    // VIEW ALL CREDITS PER EMPLOYEE
    public function show($employee_id)
    {
        $credits = LeaveCredit::where('employee_id', $employee_id)->get();

        return response()->json([
            'data' => $credits
        ]);
    }

    // UPDATE CREDIT
    public function update(Request $request, $id)
    {
        $credit = LeaveCredit::findOrFail($id);

        $credit->update([
            'activity_name' => $request->activity_name,
            'hours_rendered' => $request->hours_rendered,
            'equivalent_leave_days' => $request->equivalent_leave_days,
        ]);

  
        AuditLogger::log(
            'Leave credit updated',
            "Updated leave credit #{$credit->credits_id} for employee #{$credit->employee_id}"
        );

        return response()->json([
            'message' => 'Leave credit updated',
            'data' => $credit
        ]);
    }

public function apply(Request $request, $id)
{
    $credit = LeaveCredit::findOrFail($id);

    // Prevent duplicate application
    if ($credit->status === 'Applied') {
        return response()->json([
            'message' => 'Leave credit has already been applied.'
        ], 400);
    }

    $request->validate([
        'leave_type' => 'nullable|in:Vacation,Sick,Service Credits',
        'days' => 'nullable|numeric|min:0',
        'split' => 'nullable|boolean',
        'vacation_days' => 'nullable|numeric|min:0',
        'sick_days' => 'nullable|numeric|min:0',
    ]);

    $balance = LeaveBalance::firstOrCreate(
        ['employee_id' => $credit->employee_id],
        [
            'vacation_earned' => 0,
            'sick_earned' => 0,
            'vacation_balance' => 0,
            'sick_balance' => 0,
            'service_credits' => 0,
            'used_leave' => 0,
            'last_updated' => now(),
        ]
    );

    $creditType = strtolower(trim($credit->credit_type));
    $availableDays = (float) $credit->equivalent_leave_days;
    $isServiceCredit = $creditType === 'service credits' || $creditType === 'service';
    $split = $request->boolean('split');
    $days = (float) ($request->input('days') ?? $availableDays);
    $targetType = $request->input('leave_type');
    $vacationDays = $split
        ? (float) ($request->input('vacation_days') ?? 0)
        : ($targetType === 'Vacation' ? $days : 0);
    $sickDays = $split
        ? (float) ($request->input('sick_days') ?? 0)
        : ($targetType === 'Sick' ? $days : 0);
    $appliedDays = $split ? $vacationDays + $sickDays : $days;
    $serviceDays = $isServiceCredit
        ? ($split || $targetType !== 'Service Credits'
            ? max(0, $availableDays - $vacationDays - $sickDays)
            : min($days, $availableDays))
        : 0;

    if ($isServiceCredit && ($appliedDays <= 0 || $appliedDays > $availableDays)) {
        return response()->json([
            'message' => 'Applied allocation cannot exceed the available service credit.'
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY VACATION CREDIT
    |--------------------------------------------------------------------------
    */

    if ($creditType === 'vacation') {

        $balance->vacation_earned +=
            $credit->equivalent_leave_days;

        $balance->vacation_balance +=
            $credit->equivalent_leave_days;
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY SICK CREDIT
    |--------------------------------------------------------------------------
    */

    elseif ($creditType === 'sick') {

        $balance->sick_earned +=
            $credit->equivalent_leave_days;

        $balance->sick_balance +=
            $credit->equivalent_leave_days;
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY SERVICE CREDITS
    |--------------------------------------------------------------------------
    */

    elseif ($isServiceCredit) {

        $balance->service_credits =
            (float) ($balance->service_credits ?? 0)
            + $serviceDays;

        if ($vacationDays > 0) {
            $balance->vacation_earned += $vacationDays;
            $balance->vacation_balance += $vacationDays;
        }

        if ($sickDays > 0) {
            $balance->sick_earned += $sickDays;
            $balance->sick_balance += $sickDays;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INVALID CREDIT TYPE
    |--------------------------------------------------------------------------
    */

    else {
        return response()->json([
            'message' => 'Invalid credit type.'
        ], 422);
    }

    $balance->last_updated = now();
    $balance->save();

    /*
    |--------------------------------------------------------------------------
    | MARK CREDIT AS APPLIED
    |--------------------------------------------------------------------------
    */

    $credit->status = 'Applied';
    $credit->save();

    AuditLogger::log(
        'Leave credit applied',
        "Applied {$credit->equivalent_leave_days} {$credit->credit_type} credit day(s) to balance for employee #{$credit->employee_id}"
    );

    return response()->json([
        'message' => 'Leave credit applied successfully.',
        'data' => $credit
    ]);
}
 
}
