<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Report;
use App\Models\LeaveApplication;
use App\Models\AttendanceRecord;
use App\Models\LeaveBalance;

use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VIEW ALL REPORTS (ADMIN)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return Report::latest()->get();
    }

    public function leaveSummary(Request $request)
    {
        $leaves = LeaveApplication::with([
            'employee',
            'leaveType'
        ])
            ->whereNotNull('leave_type_id')
            ->get();

        $summary = [];

        foreach ($leaves as $leave) {

            $department = $leave->employee->department ?? 'Unknown';

            if (!isset($summary[$department])) {

                $summary[$department] = [
                    'department' => $department,
                    'total' => 0,
                    'approved' => 0,
                    'pending' => 0,
                    'disapproved' => 0,
                    'leave_types' => []
                ];
            }

            $summary[$department]['total']++;

            switch ($leave->final_status) {

                case 'approved':
                    $summary[$department]['approved']++;
                    break;

                case 'pending':
                    $summary[$department]['pending']++;
                    break;

                case 'disapproved':
                    $summary[$department]['disapproved']++;
                    break;
            }

            $type = $leave->leaveType->leave_type_name ?? 'Unknown';

            if (!isset($summary[$department]['leave_types'][$type])) {
                $summary[$department]['leave_types'][$type] = 0;
            }

            $summary[$department]['leave_types'][$type]++;
        }

        $totals = [
            'departments' => count($summary),
            'applications' => $leaves->count(),
            'approved' => $leaves->where('final_status', 'approved')->count(),
            'pending' => $leaves->where('final_status', 'pending')->count(),
            'disapproved' => $leaves->where('final_status', 'disapproved')->count(),
        ];

        return response()->json([
            'summary' => array_values($summary),
            'totals' => $totals,
        ]);
    }

    public function leaveCredits()
    {
        $balances = LeaveBalance::with('employee')->get();

        $employees = [];

        $totalVacationEarned = 0;
        $totalSickEarned = 0;

        $totalVacationBalance = 0;
        $totalSickBalance = 0;

        $totalUsedLeave = 0;

        foreach ($balances as $balance) {

            $employee = $balance->employee;

            $employees[] = [
                'employee_id' => $employee->employee_id,
                'employee_name' =>
                $employee->first_name . ' ' .
                    $employee->last_name,

                'department' => $employee->department,

                'vacation_earned' => $balance->vacation_earned,
                'sick_earned' => $balance->sick_earned,

                'vacation_balance' => $balance->vacation_balance,
                'sick_balance' => $balance->sick_balance,

                'used_leave' => $balance->used_leave
            ];

            $totalVacationEarned += $balance->vacation_earned;
            $totalSickEarned += $balance->sick_earned;

            $totalVacationBalance += $balance->vacation_balance;
            $totalSickBalance += $balance->sick_balance;

            $totalUsedLeave += $balance->used_leave;
        }

        return response()->json([

            'employees' => $employees,

            'totals' => [

                'employees' => count($employees),

                'vacation_earned' => $totalVacationEarned,
                'sick_earned' => $totalSickEarned,

                'vacation_balance' => $totalVacationBalance,
                'sick_balance' => $totalSickBalance,

                'used_leave' => $totalUsedLeave
            ]
        ]);
    }


    public function generateLeaveReport(Request $request)
    {
        $leaves = LeaveApplication::with(['employee', 'leaveType'])
            ->where('final_status', 'approved')
            ->whereNotNull('leave_type_id')
            ->get();

        $pdf = PDF::loadView('reports.leave', [
            'leaves' => $leaves
        ]);

        $fileName = 'leave_report_' . time() . '.pdf';
        $filePath = 'reports/' . $fileName;

        \Storage::disk('public')->put($filePath, $pdf->output());

        Report::create([
            'generated_by' => $request->user()->user_id,
            'report_type' => 'leave_report',
            'generated_date' => Carbon::now(),
            'file_path' => $filePath
        ]);

        return response()->json([
            'message' => 'Leave report generated successfully',
            'file_path' => $filePath
        ]);
    }

    public function employeeReport()
    {
        $employees = \App\Models\EmployeeRecord::with([
            'leaveApplications.leaveType',
            'leaveBalance'
        ])->get();

        $report = [];

        foreach ($employees as $employee) {

            $approved = $employee->leaveApplications
                ->where('final_status', 'approved')
                ->count();

            $pending = $employee->leaveApplications
                ->where('final_status', 'pending')
                ->count();

            $disapproved = $employee->leaveApplications
                ->where('final_status', 'disapproved')
                ->count();

            $report[] = [

                'employee_id' => $employee->employee_id,

                'employee_name' =>
                $employee->first_name . ' ' .
                    $employee->last_name,

                'department' => $employee->department,

                'position' => $employee->position,
                'employment_status' => $employee->employment_status,

                'approved' => $approved,

                'pending' => $pending,

                'disapproved' => $disapproved,

                'total_leave_applications' =>
                $employee->leaveApplications->count(),

                'vacation_balance' =>
                optional($employee->leaveBalance)->vacation_balance ?? 0,

                'sick_balance' =>
                optional($employee->leaveBalance)->sick_balance ?? 0,
                'used_leave' =>
                optional($employee->leaveBalance)->used_leave ?? 0,
            ];
        }

        return response()->json([
            'employees' => $report,

            'totals' => [
                'employees' => count($report),
                'approved' => collect($report)->sum('approved'),
                'pending' => collect($report)->sum('pending'),
                'disapproved' => collect($report)->sum('disapproved'),
                'applications' => collect($report)->sum('total_leave_applications'),
            ]
        ]);
    }

    public function generateAttendanceReport(Request $request)
    {
        $attendance = AttendanceRecord::with('employee')->get();

        $pdf = PDF::loadView('reports.attendance', [
            'attendance' => $attendance
        ]);

        $fileName = 'attendance_report_' . time() . '.pdf';
        $filePath = 'reports/' . $fileName;

        \Storage::disk('public')->put($filePath, $pdf->output());

        Report::create([
            'generated_by' => $request->user()->user_id,
            'report_type' => 'attendance_report',
            'generated_date' => Carbon::now(),
            'file_path' => $filePath
        ]);

        return response()->json([
            'message' => 'Attendance report generated successfully',
            'file_path' => $filePath
        ]);
    }


    public function download($id)
    {
        $report = Report::findOrFail($id);

        return response()->download(
            storage_path('app/public/' . $report->file_path)
        );
    }
}
