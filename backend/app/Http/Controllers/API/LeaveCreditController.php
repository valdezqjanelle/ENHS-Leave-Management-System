<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveCredit;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use Carbon\Carbon;

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

  
        return response()->json([
            'message' => 'Leave credit updated',
            'data' => $credit
        ]);
    }

public function apply($id)
{
    $credit = LeaveCredit::findOrFail($id);

    // Prevent duplicate application
    if ($credit->status === 'Applied') {
        return response()->json([
            'message' => 'Leave credit has already been applied.'
        ], 400);
    }

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

    elseif ($creditType === 'service credits') {

        $balance->service_credits =
            (float) ($balance->service_credits ?? 0)
            + (float) $credit->equivalent_leave_days;
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

    return response()->json([
        'message' => 'Leave credit applied successfully.',
        'data' => $credit
    ]);
}
 
}
