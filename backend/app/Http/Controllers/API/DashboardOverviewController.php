<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\AuditLog;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Read-only data for the redesigned dashboards.
 * Nothing in here changes application statuses or leave balances.
 */
class DashboardOverviewController extends Controller
{
    private const TZ = 'Asia/Manila';

    private const STATUSES = ['pending', 'approved', 'disapproved'];

    /* =========================================================
       ADMIN
    ========================================================= */

    // GET /admin/dashboard/overview
    public function adminOverview(Request $request)
    {
        $now = Carbon::now(self::TZ);
        $today = $now->toDateString();

        $monthStartUtc = $now->copy()->startOfMonth()->utc();
        $monthEndUtc = $now->copy()->endOfMonth()->utc();

        $profile = AdminProfile::where('user_id', $request->user()->user_id)->first();

        $pending = LeaveApplication::with([
                'employee:employee_id,first_name,last_name',
                'leaveType:leave_type_id,leave_type_name',
            ])
            ->where('final_status', 'pending')
            ->orderBy('date_filed')       // waiting longest first
            ->orderBy('leave_id')
            ->limit(8)
            ->get(['leave_id', 'employee_id', 'leave_type_id', 'date_filed', 'start_date', 'end_date', 'number_of_days', 'final_status'])
            ->map(fn ($l) => $this->applicationRow($l, true));

        $upcoming = LeaveApplication::with([
                'employee:employee_id,first_name,last_name',
                'leaveType:leave_type_id,leave_type_name',
            ])
            ->where('final_status', 'approved')
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(8)
            ->get(['leave_id', 'employee_id', 'leave_type_id', 'date_filed', 'start_date', 'end_date', 'number_of_days', 'final_status'])
            ->map(fn ($l) => $this->applicationRow($l, true));

        return response()->json([
            'greetingName' => $profile?->first_name ?: $request->user()->email,
            'today' => $today,
            'summary' => [
                'activePersonnel' => EmployeeRecord::whereRaw('LOWER(employment_status) = ?', ['active'])->count(),
                'pendingApplications' => LeaveApplication::where('final_status', 'pending')->count(),
                'approvedThisMonth' => LeaveApplication::where('final_status', 'approved')
                    ->whereBetween('reviewed_at', [$monthStartUtc, $monthEndUtc])
                    ->count(),
                'onLeaveToday' => LeaveApplication::where('final_status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->distinct()
                    ->count('employee_id'),
            ],
            'pendingApplications' => $pending,
            'upcomingLeave' => $upcoming,
        ]);
    }

    // GET /admin/dashboard/analytics?period=this_month|last_3_months|this_year|all
    public function adminAnalytics(Request $request)
    {
        $period = $request->query('period', 'this_year');
        $start = $this->periodStart($period);

        $statusCounts = LeaveApplication::query()
            ->when($start, fn ($q) => $q->whereDate('date_filed', '>=', $start))
            ->selectRaw('final_status, count(*) as total')
            ->groupBy('final_status')
            ->pluck('total', 'final_status');

        $statusChart = [];
        foreach (self::STATUSES as $status) {
            $statusChart[$status] = (int) ($statusCounts[$status] ?? 0);
        }

        $leaveByType = LeaveApplication::query()
            ->join('leave_types', 'leave_types.leave_type_id', '=', 'leave_applications.leave_type_id')
            ->when($start, fn ($q) => $q->whereDate('leave_applications.date_filed', '>=', $start))
            ->selectRaw('leave_types.leave_type_name as name, count(*) as total')
            ->groupBy('leave_types.leave_type_id', 'leave_types.leave_type_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => (int) $r->total])
            ->values();

        // Last 6 calendar months, independent of the period filter.
        $now = Carbon::now(self::TZ);
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->startOfMonth()->subMonthsNoOverflow($i);

            $row = LeaveApplication::query()
                ->whereDate('date_filed', '>=', $m->toDateString())
                ->whereDate('date_filed', '<=', $m->copy()->endOfMonth()->toDateString())
                ->selectRaw("count(*) as total,
                    sum(case when final_status = 'approved' then 1 else 0 end) as approved,
                    sum(case when final_status = 'disapproved' then 1 else 0 end) as disapproved,
                    sum(case when final_status = 'pending' then 1 else 0 end) as pending")
                ->first();

            $monthly[] = [
                'month' => $m->format('Y-m'),
                'label' => $m->format('M Y'),
                'total' => (int) ($row->total ?? 0),
                'approved' => (int) ($row->approved ?? 0),
                'disapproved' => (int) ($row->disapproved ?? 0),
                'pending' => (int) ($row->pending ?? 0),
            ];
        }

        $monthsWithData = collect($monthly)->filter(fn ($m) => $m['total'] > 0)->count();
        $current = end($monthly);
        $previous = $monthly[count($monthly) - 2];

        return response()->json([
            'period' => $period,
            'statusChart' => $statusChart,
            'leaveByType' => $leaveByType,
            'monthly' => $monthly,
            'hasEnoughHistory' => $monthsWithData >= 2,
            'comparison' => [
                'currentLabel' => $current['label'],
                'previousLabel' => $previous['label'],
                'current' => $current['total'],
                'previous' => $previous['total'],
                'percentChange' => $previous['total'] > 0
                    ? round((($current['total'] - $previous['total']) / $previous['total']) * 100, 1)
                    : null,
            ],
        ]);
    }

    // GET /admin/dashboard/activity
    public function adminActivity()
    {
        $logs = AuditLog::with('user:user_id,email')
            ->whereNotIn('action', ['Login', 'Logout'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->log_id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->email,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['activities' => $logs]);
    }

    // GET /admin/dashboard/calendar?month=YYYY-MM
    public function adminCalendar(Request $request)
    {
        return response()->json([
            'events' => $this->calendarEvents($request->query('month'), null, true),
        ]);
    }

    /* =========================================================
       EMPLOYEE (always scoped to the logged-in user)
    ========================================================= */

    // GET /employee/dashboard/overview
    public function employeeOverview(Request $request)
    {
        $employee = EmployeeRecord::where('user_id', $request->user()->user_id)->first();

        if (! $employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $today = Carbon::now(self::TZ)->toDateString();
        $employeeId = $employee->employee_id;

        // Balances: read straight from the existing leave_balances record.
        $balanceRow = LeaveBalance::where('employee_id', $employeeId)->first();
        $balances = $balanceRow ? [
            'vacation' => (float) $balanceRow->vacation_balance,
            'sick' => (float) $balanceRow->sick_balance,
            'local' => (float) $balanceRow->service_credits,
            'lastUpdated' => $balanceRow->last_updated?->toIso8601String(),
        ] : null;

        $statusCounts = LeaveApplication::where('employee_id', $employeeId)
            ->selectRaw('final_status, count(*) as total')
            ->groupBy('final_status')
            ->pluck('total', 'final_status');

        $statusChart = [];
        foreach (self::STATUSES as $status) {
            $statusChart[$status] = (int) ($statusCounts[$status] ?? 0);
        }

        $recent = LeaveApplication::with('leaveType:leave_type_id,leave_type_name')
            ->where('employee_id', $employeeId)
            ->orderByDesc('date_filed')
            ->orderByDesc('leave_id')
            ->limit(8)
            ->get(['leave_id', 'employee_id', 'leave_type_id', 'date_filed', 'start_date', 'end_date', 'number_of_days', 'final_status'])
            ->map(fn ($l) => $this->applicationRow($l, false));

        $upcoming = LeaveApplication::with('leaveType:leave_type_id,leave_type_name')
            ->where('employee_id', $employeeId)
            ->where('final_status', 'approved')
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(5)
            ->get(['leave_id', 'employee_id', 'leave_type_id', 'date_filed', 'start_date', 'end_date', 'number_of_days', 'final_status'])
            ->map(fn ($l) => $this->applicationRow($l, false));

        // Simple "status updates": own applications that have been reviewed.
        $updates = LeaveApplication::with('leaveType:leave_type_id,leave_type_name')
            ->where('employee_id', $employeeId)
            ->whereIn('final_status', ['approved', 'disapproved'])
            ->orderByRaw('COALESCE(reviewed_at, updated_at) DESC')
            ->limit(4)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->leave_id,
                'leave_type' => $l->leaveType?->leave_type_name,
                'status' => $l->final_status,
                'reason' => $l->final_status === 'disapproved' ? $l->disapproval_reason : null,
                'reviewed_at' => ($l->reviewed_at ?? $l->updated_at)?->toIso8601String(),
            ]);

        return response()->json([
            'greetingName' => $employee->first_name,
            'today' => $today,
            'balances' => $balances,
            'summary' => [
                'total' => array_sum($statusChart),
                'pending' => $statusChart['pending'],
                'approved' => $statusChart['approved'],
                'disapproved' => $statusChart['disapproved'],
            ],
            'statusChart' => $statusChart,
            'recentApplications' => $recent,
            'upcomingLeave' => $upcoming,
            'statusUpdates' => $updates,
        ]);
    }

    // GET /employee/dashboard/calendar?month=YYYY-MM
    public function employeeCalendar(Request $request)
    {
        $employee = EmployeeRecord::where('user_id', $request->user()->user_id)->first();

        if (! $employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        return response()->json([
            'events' => $this->calendarEvents($request->query('month'), $employee->employee_id, false),
        ]);
    }

    /* =========================================================
       HELPERS
    ========================================================= */

    private function periodStart(string $period): ?string
    {
        $now = Carbon::now(self::TZ);

        return match ($period) {
            'this_month' => $now->copy()->startOfMonth()->toDateString(),
            'last_3_months' => $now->copy()->startOfMonth()->subMonthsNoOverflow(2)->toDateString(),
            'this_year' => $now->copy()->startOfYear()->toDateString(),
            default => null, // "all"
        };
    }

    private function calendarEvents(?string $month, ?int $employeeId, bool $withEmployee): array
    {
        $first = ($month && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month))
            ? Carbon::parse($month . '-01', self::TZ)
            : Carbon::now(self::TZ)->startOfMonth();

        $last = $first->copy()->endOfMonth();

        $query = LeaveApplication::with('leaveType:leave_type_id,leave_type_name')
            ->where('final_status', 'approved') // pending is never shown as a confirmed absence
            ->whereDate('start_date', '<=', $last->toDateString())
            ->whereDate('end_date', '>=', $first->toDateString())
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->orderBy('start_date')
            ->limit(300);

        if ($withEmployee) {
            $query->with('employee:employee_id,first_name,last_name');
        }

        return $query->get()->map(fn ($l) => [
            'id' => $l->leave_id,
            'employee' => $withEmployee ? $this->fullName($l->employee) : null,
            'leave_type' => $l->leaveType?->leave_type_name,
            'start_date' => $l->start_date?->toDateString(),
            'end_date' => $l->end_date?->toDateString(),
            'days' => $l->number_of_days,
        ])->all();
    }

    private function applicationRow($leave, bool $withEmployee): array
    {
        return [
            'id' => $leave->leave_id,
            'employee' => $withEmployee ? $this->fullName($leave->employee) : null,
            'leave_type' => $leave->leaveType?->leave_type_name,
            'date_filed' => $leave->date_filed?->toDateString(),
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'days' => $leave->number_of_days,
            'status' => $leave->final_status,
        ];
    }

    private function fullName($employee): string
    {
        if (! $employee) {
            return 'Unknown employee';
        }

        return trim($employee->first_name . ' ' . $employee->last_name);
    }
}
