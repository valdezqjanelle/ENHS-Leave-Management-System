<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveApplication;
use App\Models\LeaveAttachment;
use App\Models\EmployeeRecord;
use App\Models\LeaveBalance;
use App\Models\LeaveType;

class LeaveController extends Controller
{
    /*
    | EMPLOYEE: SUBMIT LEAVE
    */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'date_filed' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'number_of_days' => 'required|integer|min:1',
            'commutation' => 'nullable|string',
            'reason' => 'required|string',

            // ENHS FIELDS
            'vacation_location_type' => 'nullable|string',
            'vacation_location' => 'nullable|string',
            'sick_type' => 'nullable|string',
            'illness' => 'nullable|string',

            'masters_degree' => 'nullable|boolean',
            'board_exam_review' => 'nullable|boolean',

            'monetization' => 'nullable|boolean',
            'terminal_leave' => 'nullable|boolean',

            'other_purpose' => 'nullable|string',

            'certification_as_of' => 'nullable|date',

            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $employee = EmployeeRecord::where('user_id', $request->user()->user_id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $balance = LeaveBalance::where('employee_id', $employee->employee_id)->first();

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $isVacation = str_contains(
            strtolower($leaveType->leave_type_name),
            'vacation'
        );

        $isSick = str_contains(
            strtolower($leaveType->leave_type_name),
            'sick'
        );
        $leave = LeaveApplication::create([
            'employee_id' => $employee->employee_id,
            'leave_type_id' => $request->leave_type_id,

            'date_filed' => $request->date_filed,

            // VACATION
            'vacation_location_type' => $request->vacation_location_type,
            'vacation_location' => $request->vacation_location,

            // SICK
            'sick_type' => $request->sick_type,
            'illness' => $request->illness,

            // STUDY
            'masters_degree' => $request->masters_degree ?? false,
            'board_exam_review' => $request->board_exam_review ?? false,

            // OTHER
            'monetization' => $request->monetization ?? false,
            'terminal_leave' => $request->terminal_leave ?? false,
            'other_purpose' => $request->other_purpose,

            // DATES
            'number_of_days' => $request->number_of_days,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,

            'commutation' => $request->commutation,
            'reason' => $request->reason,

            // CERTIFICATION
            'certification_as_of' => $request->certification_as_of,

            'vacation_total_earned' => $isVacation
                ? $balance?->vacation_balance
                : null,

            'vacation_less_application' => $isVacation
                ? $request->number_of_days
                : null,

            'vacation_balance' => $isVacation
                ? ($balance?->vacation_balance - $request->number_of_days)
                : null,

            'sick_total_earned' => $isSick
                ? $balance?->sick_balance
                : null,

            'sick_less_application' => $isSick
                ? $request->number_of_days
                : null,

            'sick_balance' => $isSick
                ? ($balance?->sick_balance - $request->number_of_days)
                : null,

            'recommendation_status' => 'pending',
            'final_status' => 'pending',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('leave_attachments', 'public');

                LeaveAttachment::create([
                    'leave_id' => $leave->leave_id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message' => 'Leave application submitted successfully',
            'data' => $leave->load(['employee', 'leaveType', 'attachments'])
        ], 201);
    }

    /*
    | EMPLOYEE: VIEW OWN LEAVES
    */
    public function myLeaves(Request $request)
    {
        $employee = EmployeeRecord::where('user_id', $request->user()->user_id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        return LeaveApplication::with(['employee', 'leaveType', 'attachments'])
            ->where('employee_id', $employee->employee_id)
            ->oldest()
            ->get();
    }

    /*
    | EMPLOYEE: VIEW SINGLE
    */
    public function myLeave($id, Request $request)
    {
        $employee = EmployeeRecord::where('user_id', $request->user()->user_id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        return LeaveApplication::with(['employee', 'leaveType', 'attachments'])
            ->where('employee_id', $employee->employee_id)
            ->where('leave_id', $id)
            ->firstOrFail();
    }

    /*
    | ADMIN: VIEW ALL
    */
    public function index()
    {
        return LeaveApplication::with(['employee', 'leaveType', 'attachments'])
            ->latest()
            ->get();
    }

    /*
    | ADMIN: VIEW ONE
    */
    public function show($id)
    {
        return LeaveApplication::with(['employee', 'leaveType', 'attachments'])
            ->findOrFail($id);
    }

    /*
    | ADMIN: UPDATE STATUS
    */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([

            // Certification
            'certification_as_of' => 'nullable|date',

            'vacation_total_earned' => 'nullable|numeric',
            'vacation_less_application' => 'nullable|numeric',
            'vacation_balance' => 'nullable|numeric',

            'sick_total_earned' => 'nullable|numeric',
            'sick_less_application' => 'nullable|numeric',
            'sick_balance' => 'nullable|numeric',

            // Recommendation
            'recommendation_status' => 'nullable|string',
            'recommendation_reason' => 'nullable|string',

            // Approval
            'days_with_pay' => 'nullable|integer',
            'days_without_pay' => 'nullable|integer',
            'other_approval' => 'nullable|string',

            // Final
            'final_status' => 'nullable|string',
            'disapproval_reason' => 'nullable|string',
            'admin_remarks' => 'nullable|string',
        ]);

        $leave = LeaveApplication::findOrFail($id);

        $previousStatus = $leave->final_status;

        $leave->update([

            // Certification
            'certification_as_of' => $request->certification_as_of,

            'vacation_total_earned' => $request->vacation_total_earned,
            'vacation_less_application' => $request->vacation_less_application,
            'vacation_balance' => $request->vacation_balance,

            'sick_total_earned' => $request->sick_total_earned,
            'sick_less_application' => $request->sick_less_application,
            'sick_balance' => $request->sick_balance,

            // Recommendation
            'recommendation_status' =>
            $request->recommendation_status ?? $leave->recommendation_status,

            'recommendation_reason' =>
            $request->recommendation_reason,

            // Approval
            'days_with_pay' =>
            $request->days_with_pay,

            'days_without_pay' =>
            $request->days_without_pay,

            'other_approval' =>
            $request->other_approval,

            // Final
            'final_status' =>
            $request->final_status ?? $leave->final_status,

            'disapproval_reason' =>
            $request->disapproval_reason,

            'admin_remarks' =>
            $request->admin_remarks,
        ]);

        if (
            $previousStatus !== 'approved' &&
            $leave->final_status === 'approved'
        ) {
            $this->deductLeaveBalance($leave);
        }

        return response()->json([
            'message' => 'Leave updated successfully',
            'data' => $leave->load([
                'employee',
                'leaveType',
                'attachments'
            ])
        ]);
    }

    private function deductLeaveBalance($leave)
    {
        $balance = LeaveBalance::where(
            'employee_id',
            $leave->employee_id
        )->first();

        if (!$balance) {
            return;
        }

        $days = (int) $leave->number_of_days;

        $leaveName = strtolower(
            $leave->leaveType->leave_type_name
        );

        if (str_contains($leaveName, 'vacation')) {

            $balance->vacation_balance -= $days;

            if ($balance->vacation_balance < 0) {
                $balance->vacation_balance = 0;
            }
        } elseif (str_contains($leaveName, 'sick')) {

            $balance->sick_balance -= $days;

            if ($balance->sick_balance < 0) {
                $balance->sick_balance = 0;
            }
        }

        $balance->used_leave =
            ($balance->used_leave ?? 0) + $days;

        $balance->last_updated = now();

        $balance->save();
    }
}
