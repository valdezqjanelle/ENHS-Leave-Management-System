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
use App\Models\Department;

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

    /**
     * Optional Leave Summary filters. Each one narrows the application set itself, so cards,
     * department rows, leave-type breakdowns and charts are all computed from the same records.
     */
    private function summaryFilters(Request $request): array
    {
        $filters = $request->validate([
            'department_id' => ['nullable', function ($attribute, $value, $fail) {
                $valid = $value === 'unassigned'
                    || (is_scalar($value) && ctype_digit((string) $value)
                        && Department::whereKey((int) $value)->exists());
                if (!$valid) $fail('The selected department is invalid.');
            }],
            'status' => ['nullable', 'in:approved,pending,disapproved'],
            'leave_type_id' => ['nullable', 'integer', 'exists:leave_types,leave_type_id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        // Employee search: every whitespace/comma separated word must match a name part.
        $tokens = preg_split('/[\s,]+/u', mb_strtolower(trim((string) ($filters['search'] ?? ''))), -1, PREG_SPLIT_NO_EMPTY);

        return [
            'department_id' => isset($filters['department_id']) ? (string) $filters['department_id'] : null,
            'status' => $filters['status'] ?? null,
            'leave_type_id' => isset($filters['leave_type_id']) ? (int) $filters['leave_type_id'] : null,
            'search' => $tokens ? implode(' ', array_slice($tokens, 0, 5)) : null,
        ];
    }

    /**
     * The one query behind every application-based report (Leave Summary, Leave Application History):
     * leave dates overlapping the period, then status, leave type, current department, employee search
     * and employee scope. Using it everywhere keeps counts, tables, exports and print output consistent.
     */
    private function filteredApplications(array $dates, array $filters, bool $includeInactive)
    {
        return LeaveApplication::with([
            'employee' => fn ($q) => $q->withTrashed()->with('department'),
            'leaveType',
        ])
            ->when($dates['start_date'] ?? null, fn ($q, $date) => $q->where(function ($query) use ($date) {
                $query->whereDate('end_date', '>=', $date)
                    ->orWhere(function ($missingEnd) use ($date) {
                        $missingEnd->whereNull('end_date')->whereDate('start_date', '>=', $date);
                    });
            }))
            ->when($dates['end_date'] ?? null, fn ($q, $date) => $q->whereDate('start_date', '<=', $date))
            ->when($filters['status'] !== null, fn ($q) => $q->whereRaw('LOWER(TRIM(final_status)) = ?', [$filters['status']]))
            ->when($filters['leave_type_id'] !== null, fn ($q) => $q->where('leave_type_id', $filters['leave_type_id']))
            ->when($filters['department_id'] !== null, function ($q) use ($filters) {
                // Current department of the linked employee (archived employees included).
                $department = $filters['department_id'];
                if ($department === 'unassigned') {
                    $q->where(function ($query) {
                        $query->whereDoesntHave('employee', fn ($e) => $e->withTrashed())
                            ->orWhereHas('employee', fn ($e) => $e->withTrashed()->whereNull('department_id'));
                    });
                } else {
                    $q->whereHas('employee', fn ($e) => $e->withTrashed()->where('department_id', (int) $department));
                }
            })
            ->when($filters['search'] !== null, function ($q) use ($filters) {
                $q->whereHas('employee', function ($e) use ($filters) {
                    $e->withTrashed();
                    foreach (explode(' ', $filters['search']) as $token) {
                        // "!" is the LIKE escape character so % and _ typed by the user stay literal.
                        $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token) . '%';
                        $e->where(function ($name) use ($like) {
                            foreach (['first_name', 'middle_name', 'last_name'] as $column) {
                                $name->orWhereRaw("LOWER(employee_records.{$column}) LIKE ? ESCAPE '!'", [$like]);
                            }
                        });
                    }
                });
            })
            ->get()
            ->filter(function ($leave) use ($includeInactive) {
                if (!$leave->employee) return true;
                return $includeInactive || (!$leave->employee->trashed()
                    && strtolower(trim((string) $leave->employee->employment_status)) === 'active');
            })->values();
    }

    public function leaveSummary(Request $request)
    {
        $dates = $this->reportDates($request);
        $request->validate(['include_inactive' => 'sometimes|boolean']);
        $filters = $this->summaryFilters($request);
        // Historical applications are included by default, even after departure.
        $includeInactive = $request->boolean('include_inactive', true);
        $leaves = $this->filteredApplications($dates, $filters, $includeInactive);

        $summary = [];
        $monthly = [];
        $totals = ['departments' => 0, 'applications' => 0, 'total_days' => 0,
            'approved_days' => 0, 'approved' => 0, 'pending' => 0,
            'disapproved' => 0, 'other' => 0];
        foreach ($leaves as $leave) {
            $departmentId = $leave->employee?->department?->department_id;
            $key = $departmentId === null ? 'unassigned' : 'department:' . $departmentId;
            if (!isset($summary[$key])) {
                $summary[$key] = [
                    'department_id' => $departmentId,
                    'department' => $leave->employee?->department?->department_name ?? 'Unassigned',
                    'total' => 0, 'total_days' => 0, 'approved_days' => 0,
                    'approved' => 0, 'pending' => 0, 'disapproved' => 0,
                    'other' => 0, 'leave_types' => [],
                ];
            }
            $status = strtolower(trim((string) $leave->final_status));
            if (!in_array($status, ['approved', 'pending', 'disapproved'], true)) $status = 'other';
            // Integer thousandths preserve half days and three-decimal equivalents.
            $dayUnits = (int) round((float) ($leave->number_of_days ?? 0) * 1000);
            $summary[$key]['total']++;
            $summary[$key]['total_days'] += $dayUnits;
            $summary[$key][$status]++;
            $totals['applications']++;
            $totals['total_days'] += $dayUnits;
            $totals[$status]++;
            if ($status === 'approved') {
                $summary[$key]['approved_days'] += $dayUnits;
                $totals['approved_days'] += $dayUnits;
            }
            $type = $leave->leaveType?->leave_type_name ?? 'Unspecified';
            $summary[$key]['leave_types'][$type] = ($summary[$key]['leave_types'][$type] ?? 0) + 1;

            // Filing month (date_filed) with the application's CURRENT status; earlier statuses are not stored.
            $month = $leave->date_filed ? Carbon::parse($leave->date_filed)->format('Y-m') : null;
            if ($month !== null) {
                $monthly[$month] ??= ['month' => $month, 'submitted' => 0, 'approved' => 0,
                    'pending' => 0, 'disapproved' => 0, 'other' => 0];
                $monthly[$month]['submitted']++;
                $monthly[$month][$status]++;
            }
        }
        ksort($monthly);
        $formatDays = fn ($units) => number_format($units / 1000, 3, '.', '');
        foreach ($summary as &$row) {
            $row['total_days'] = $formatDays($row['total_days']);
            $row['approved_days'] = $formatDays($row['approved_days']);
        }
        unset($row);
        $totals['departments'] = count($summary);
        $totals['total_days'] = $formatDays($totals['total_days']);
        $totals['approved_days'] = $formatDays($totals['approved_days']);
        $rows = array_values($summary);
        usort($rows, fn ($a, $b) => strcasecmp($a['department'], $b['department']));

        return response()->json([
            'report_version' => 'leave-summary-v2',
            'summary' => $rows, 'totals' => $totals,
            'monthly' => array_values($monthly),
            'generated_at' => Carbon::now('Asia/Manila')->toIso8601String(),
            'filters' => [
                'start_date' => $dates['start_date'] ?? null,
                'end_date' => $dates['end_date'] ?? null,
                'include_inactive' => $includeInactive,
                'department_id' => $filters['department_id'],
                'status' => $filters['status'],
                'leave_type_id' => $filters['leave_type_id'],
                'search' => $filters['search'],
                'date_basis' => 'Applications with leave dates overlapping the selected period.',
                'days_basis' => 'Full requested days per matching application, not days restricted to the period or credits deducted.',
                'department_basis' => 'Current department of the linked employee; historical department snapshots are not available.',
                'record_scope' => 'Non-deleted leave applications. Missing employee/department links are shown as Unassigned.',
                'monthly_basis' => 'Applications grouped by the month they were filed, split by their current status. Past status changes are not stored, so this is not a history of approvals.',
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * Leave Application History: one row per matching application, using the same filters as Leave Summary.
     * Only recorded information is returned. The system does not store a Division Office outcome or earlier
     * status changes, so none is reported; status is the application's current status.
     */
    public function leaveHistory(Request $request)
    {
        $dates = $this->reportDates($request);
        $request->validate(['include_inactive' => 'sometimes|boolean']);
        $filters = $this->summaryFilters($request);
        $includeInactive = $request->boolean('include_inactive', true);
        $leaves = $this->filteredApplications($dates, $filters, $includeInactive)
            ->sortBy([['start_date', 'desc'], ['leave_id', 'desc']])->values();

        $totals = ['records' => 0, 'approved' => 0, 'pending' => 0, 'disapproved' => 0, 'other' => 0,
            'requested_days' => 0, 'approved_days' => 0];
        $dayText = fn ($units) => number_format($units / 1000, 3, '.', '');
        $dateText = fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null;
        $recommendations = ['pending', 'approved', 'disapproved'];

        $records = $leaves->map(function ($leave) use (&$totals, $dayText, $dateText, $recommendations) {
            $employee = $leave->employee;
            $status = strtolower(trim((string) $leave->final_status));
            if (!in_array($status, ['approved', 'pending', 'disapproved'], true)) $status = 'other';
            $units = (int) round((float) ($leave->number_of_days ?? 0) * 1000);
            $totals['records']++;
            $totals[$status]++;
            $totals['requested_days'] += $units;
            if ($status === 'approved') $totals['approved_days'] += $units;
            $recommendation = strtolower(trim((string) $leave->recommendation_status));
            $text = fn ($value) => ($value === null || trim((string) $value) === '') ? null : trim((string) $value);

            return [
                'leave_id' => $leave->leave_id,
                'employee_id' => $leave->employee_id,
                'employee_name' => $employee
                    ? (implode(', ', array_filter([$employee->last_name, $employee->first_name])) ?: 'Unknown employee')
                    : 'Unknown employee',
                'employee_state' => !$employee ? 'unlinked'
                    : ($employee->trashed() ? 'archived'
                        : (strtolower(trim((string) $employee->employment_status)) === 'active' ? 'active' : 'inactive')),
                'department_id' => $employee?->department?->department_id,
                'department' => $employee?->department?->department_name ?? 'Unassigned',
                'leave_type_id' => $leave->leave_type_id,
                'leave_type' => $leave->leaveType?->leave_type_name ?? 'Unspecified',
                'date_filed' => $dateText($leave->date_filed),
                'start_date' => $dateText($leave->start_date),
                'end_date' => $dateText($leave->end_date),
                'requested_days' => $dayText($units),
                'status' => $status,
                'status_changed_at' => $leave->reviewed_at
                    ? Carbon::parse($leave->reviewed_at)->timezone('Asia/Manila')->toIso8601String() : null,
                'recommendation_status' => in_array($recommendation, $recommendations, true) ? $recommendation : null,
                'recommendation_reason' => $text($leave->recommendation_reason),
                'days_with_pay' => $leave->days_with_pay,
                'days_without_pay' => $leave->days_without_pay,
                'other_approval' => $text($leave->other_approval),
                'disapproval_reason' => $text($leave->disapproval_reason),
                'admin_remarks' => $text($leave->admin_remarks),
            ];
        })->all();

        $totals['requested_days'] = $dayText($totals['requested_days']);
        $totals['approved_days'] = $dayText($totals['approved_days']);

        return response()->json([
            'report_version' => 'leave-history-v1',
            'records' => $records,
            'totals' => $totals,
            'generated_at' => Carbon::now('Asia/Manila')->toIso8601String(),
            'filters' => [
                'start_date' => $dates['start_date'] ?? null,
                'end_date' => $dates['end_date'] ?? null,
                'include_inactive' => $includeInactive,
                'department_id' => $filters['department_id'],
                'status' => $filters['status'],
                'leave_type_id' => $filters['leave_type_id'],
                'search' => $filters['search'],
                'date_basis' => 'Applications with leave dates overlapping the selected period.',
                'days_basis' => 'Requested days exactly as recorded on each application.',
                'department_basis' => 'Current department of the linked employee; historical department snapshots are not available.',
                'status_basis' => 'Current application status. Earlier status changes are not stored; "status changed" is the time of the most recent change when recorded.',
                'not_recorded' => ['Division Office outcome', 'Status change history', 'Per-approval dates and signatories'],
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    public function leaveCredits()
    {
        // Current balance snapshot, using the same rules as /leave-balances (the Leave
        // Balances page): every non-archived employee is listed, active or inactive, and
        // an employee with no leave_balances row is flagged instead of reported as 0.
        $units = fn ($value) => (int) round((float) ($value ?? 0) * 1000);
        $days = fn (int $thousandths) => round($thousandths / 1000, 3);
        $fields = ['vacation_earned', 'sick_earned', 'vacation_balance',
            'sick_balance', 'service_credits', 'used_leave'];
        $sums = array_fill_keys([...$fields, 'total_remaining'], 0);
        $withoutRecord = 0;
        $rows = [];

        $employees = EmployeeRecord::with(['department', 'leaveBalance'])
            ->orderBy('employee_id')
            ->get();

        foreach ($employees as $employee) {
            $balance = $employee->leaveBalance;
            $hasRecord = $balance !== null;
            if (!$hasRecord) $withoutRecord++;

            $amount = [];
            foreach ($fields as $field) {
                $amount[$field] = $units($balance?->{$field});
            }
            // Same rule as the Leave Balances page: Vacation + Sick + Local (service) credits.
            $amount['total_remaining'] = $amount['vacation_balance']
                + $amount['sick_balance'] + $amount['service_credits'];
            foreach ($amount as $field => $value) {
                $sums[$field] += $value;
            }

            $rows[] = [
                'employee_id' => $employee->employee_id,
                'employee_name' => implode(', ', array_filter([$employee->last_name, $employee->first_name])) ?: 'Unknown employee',
                'department_name' => $employee->department?->department_name ?? 'Unassigned',
                'has_balance_record' => $hasRecord,
            ] + array_map($days, $amount);
        }

        return response()->json([
            'report_version' => 'leave-credits-v2',
            'employees' => $rows,
            'totals' => [
                'employees' => count($rows),
                'employees_without_balance_record' => $withoutRecord,
            ] + array_map($days, $sums),
            'generated_at' => Carbon::now('Asia/Manila')->toIso8601String(),
            'filters' => [
                'date_basis' => 'Current balance snapshot; date filters do not apply and balances are not historical.',
                'employee_scope' => 'All non-archived employees, active and inactive. Archived employees are not listed.',
                'missing_record_basis' => 'Employees without a leave balance record are listed with has_balance_record = false; their zeros mean no record, not a recorded zero balance.',
                'total_remaining_basis' => 'Vacation balance + sick balance + local (service) credits, as on the Leave Balances page.',
            ],
        ])->header('Cache-Control', 'no-store, private');
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