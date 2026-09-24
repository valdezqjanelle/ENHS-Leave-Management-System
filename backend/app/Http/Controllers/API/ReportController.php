<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Report;
use App\Models\LeaveApplication;
use App\Models\AttendanceRecord;
use App\Models\LeaveBalance;
use App\Models\LeaveCredit;
use App\Models\EmployeeRecord;

use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{

    public function index()
    {
        return Report::latest()->get();
    }

    private function reportDates(Request $request): array
    {
        return $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
    }

    public function leaveSummary(Request $request)
    {
        $dates = $this->reportDates($request);
        $leaves = LeaveApplication::with([
            'employee.department',
            'leaveType'
        ])
            ->whereNotNull('leave_type_id')
            ->when($dates['start_date'] ?? null, fn ($q, $date) => $q->where(function ($query) use ($date) {
                $query->whereDate('end_date', '>=', $date)
                    ->orWhere(function ($missingEnd) use ($date) {
                        $missingEnd->whereNull('end_date')->whereDate('start_date', '>=', $date);
                    });
            }))
            ->when($dates['end_date'] ?? null, fn ($q, $date) => $q->whereDate('start_date', '<=', $date))
            ->get();

        $summary = [];

        foreach ($leaves as $leave) {

            $department = $leave->employee?->department?->department_name ?? 'Unknown';

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
        // Include employees with balances even when they have no credit entries.
        $employeeIds = LeaveCredit::query()->pluck('employee_id')
            ->merge(LeaveBalance::query()->pluck('employee_id'))->unique();

        $employees = EmployeeRecord::with(['department', 'leaveBalance'])
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->map(function ($employee) {
                $balance = $employee->leaveBalance;
                return [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'department_name' => $employee->department?->department_name ?? 'Unknown',
                    'vacation_earned' => (float) ($balance?->vacation_earned ?? 0),
                    'sick_earned' => (float) ($balance?->sick_earned ?? 0),
                    'vacation_balance' => (float) ($balance?->vacation_balance ?? 0),
                    'sick_balance' => (float) ($balance?->sick_balance ?? 0),
                    'used_leave' => (float) ($balance?->used_leave ?? 0),
                ];
            })->values();

        return response()->json([
            'employees' => $employees,
            'totals' => [
                'employees' => $employees->count(),
                'vacation_earned' => $employees->sum('vacation_earned'),
                'sick_earned' => $employees->sum('sick_earned'),
                'vacation_balance' => $employees->sum('vacation_balance'),
                'sick_balance' => $employees->sum('sick_balance'),
                'used_leave' => $employees->sum('used_leave'),
            ],
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
        'position',
        'department',
        'leaveBalance',
        'leaveApplications',
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

            'department_name' => $employee->department?->department_name ?? 'Unknown',
            'department' => $employee->department?->department_name ?? 'Unknown',

            'position' => $employee->position
                ? [
                    'id' => $employee->position->id,
                    'name' => $employee->position->name,
                ]
                : null,

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
            'applications' =>
                collect($report)->sum('total_leave_applications'),
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
