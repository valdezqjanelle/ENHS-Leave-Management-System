<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveCredit;
use App\Models\LeaveBalance;
use App\Models\EmployeeRecord;
use Carbon\Carbon;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class LeaveCreditController extends Controller
{
    // GET SEEDED CONVERSION REFERENCE
    public function conversionReference()
    {
        try {
            $rows = DB::table('work_time_conversions')
                ->select('unit', 'quantity', 'equivalent_days')
                ->get();

            $hour = $rows->first(function ($row) {
                return $row->unit === 'hour' && (int) $row->quantity === 1;
            });
            $minutes = $rows->where('unit', 'minute')->keyBy('quantity');
            if (!$hour || !is_numeric($hour->equivalent_days)) {
                return response()->json([
                    'code' => 'CONVERSION_DATA_INCOMPLETE',
                    'message' => 'Conversion reference is incomplete in the backend database. Run WorkTimeConversionSeeder there.',
                ], 503);
            }
            for ($minute = 1; $minute <= 60; $minute++) {
                if (!$minutes->has($minute)
                    || !is_numeric($minutes->get($minute)->equivalent_days)) {
                    return response()->json([
                        'code' => 'CONVERSION_DATA_INCOMPLETE',
                        'message' => 'Conversion reference is incomplete in the backend database. Run WorkTimeConversionSeeder there.',
                    ], 503);
                }
            }
            return response()->json($rows);
        } catch (QueryException $exception) {
            Log::error('Conversion reference database error', ['exception' => $exception]);
            $state = (string) ($exception->errorInfo[0] ?? $exception->getCode());
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);
            if ($state === '42P01' || $driverCode === 1146) {
                return response()->json([
                    'code' => 'CONVERSION_TABLE_MISSING',
                    'message' => 'The deployed backend cannot find work_time_conversions. Check its database connection and run the migration on that database.',
                ], 503);
            }
            if ($state === '42703' || $driverCode === 1054) {
                return response()->json([
                    'code' => 'CONVERSION_COLUMNS_MISMATCH',
                    'message' => 'The backend table must have unit, quantity and equivalent_days columns. Check the deployed database schema.',
                ], 503);
            }
            if ($state === '42501' || in_array($driverCode, [1044, 1045, 1142], true)) {
                return response()->json([
                    'code' => 'CONVERSION_ACCESS_DENIED',
                    'message' => 'The backend database account cannot read the conversion table. Check database access permissions.',
                ], 503);
            }
            return response()->json([
                'code' => 'CONVERSION_DATABASE_ERROR',
                'message' => 'The backend could not query the conversion table. Check the Conversion reference database error entry in Laravel logs.',
            ], 500);
        } catch (\Throwable $exception) {
            Log::error('Conversion reference runtime error', ['exception' => $exception]);
            return response()->json([
                'code' => 'CONVERSION_RUNTIME_ERROR',
                'message' => 'Conversion endpoint failed. Check the Conversion reference runtime error entry in Laravel logs.',
            ], 500);
        }
    }

    // ADD CREDIT
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'activity_name' => 'required|string|max:255',
            'hours_rendered' => 'required|numeric|min:0|max:100000',
            'equivalent_leave_days' => 'required|numeric|min:0|max:100000',
            'credit_type' => 'required|in:Service',
        ]);
        $units = (int) round((float) $data['hours_rendered'] * 60);
        if ($units < 1) {
                return response()->json(['message' => 'Rendered time must be at least one minute.'], 422);
        }
            $references = DB::table('work_time_conversions')->get()->keyBy(fn ($r) => $r->unit . ':' . $r->quantity);
            $hours = intdiv($units, 60);
            $minutes = $units % 60;
            $hourRate = $references->get('hour:1');
            $minuteRate = $minutes ? $references->get('minute:' . $minutes) : null;
            if (!$hourRate || ($minutes && !$minuteRate)) {
                return response()->json(['message' => 'The work-time conversion reference is unavailable or incomplete.'], 503);
            }
            $calculated = (int) round((float) $hourRate->equivalent_days * 1000) * $hours
                + ($minutes ? (int) round((float) $minuteRate->equivalent_days * 1000) : 0);
            if ($calculated < 1 || abs($calculated - (int) round((float) $data['equivalent_leave_days'] * 1000)) > 0) {
                return response()->json(['message' => 'Equivalent days do not match the approved conversion reference.', 'errors' => ['equivalent_leave_days' => ['Refresh the preview and submit again.']]], 422);
            }
            $data['equivalent_leave_days'] = number_format($calculated / 1000, 3, '.', '');

        $credit = DB::transaction(function () use ($data, $request, $units) {
            EmployeeRecord::where('employee_id', $data['employee_id'])->lockForUpdate()->firstOrFail();
            $balance = LeaveBalance::withTrashed()->where('employee_id', $data['employee_id'])->lockForUpdate()->first();
            if ($balance?->trashed()) $balance->restore();
            $balance ??= new LeaveBalance(['employee_id' => $data['employee_id']]);
            foreach (['vacation_earned', 'sick_earned', 'vacation_balance', 'sick_balance', 'service_credits', 'used_leave'] as $field) {
                $balance->{$field} ??= 0;
            }
            $days = (int) round((float) $data['equivalent_leave_days'] * 1000);
            $balance->service_credits = number_format(((int) round((float) $balance->service_credits * 1000) + $days) / 1000, 3, '.', '');
            $bucket = 'service';
            $balance->last_updated = now();
            $balance->save();
            $credit = LeaveCredit::create([
                'employee_id' => $data['employee_id'], 'activity_name' => $data['activity_name'],
                'hours_rendered' => number_format($units / 60, 4, '.', ''),
                'equivalent_leave_days' => $data['equivalent_leave_days'], 'credit_type' => $data['credit_type'],
                'status' => 'Applied', 'posted_bucket' => $bucket, 'posted_days' => $data['equivalent_leave_days'],
                'date_recorded' => Carbon::now(), 'recorded_by' => $request->user()->user_id,
            ]);
            AuditLogger::log('Leave credit added', "Added {$credit->equivalent_leave_days} {$credit->credit_type} credit day(s) for employee #{$credit->employee_id} ({$credit->activity_name})");
            return $credit;
        });

        return response()->json([
            'message' => 'Leave credit added successfully',
            'data' => $credit
        ]);
    }

    // VIEW ALL CREDITS
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
        $credits = LeaveCredit::with('employee')->where('employee_id', $employee_id)->orderByDesc('credits_id')->get();

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

    // DELETE CREDIT
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $credit = LeaveCredit::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($credit->status === 'Applied' && !$credit->posted_bucket) {
                throw \Illuminate\Validation\ValidationException::withMessages(['credit' => 'This historical credit has no recorded balance allocation and requires a balance review before it can be revoked.']);
            }
            if ($credit->status === 'Applied' && $credit->posted_bucket && $credit->posted_days) {
                $balance = LeaveBalance::where('employee_id', $credit->employee_id)->lockForUpdate()->firstOrFail();
                $units = (int) round((float) $credit->posted_days * 1000);
                $field = match ($credit->posted_bucket) { 'vacation' => 'vacation_balance', 'sick' => 'sick_balance', default => 'service_credits' };
                if ((int) round((float) $balance->{$field} * 1000) < $units) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['credit' => 'This credit has already been used and cannot be revoked without a balance review.']);
                }
                $balance->{$field} = number_format(((int) round((float) $balance->{$field} * 1000) - $units) / 1000, 3, '.', '');
                if ($credit->posted_bucket === 'vacation') $balance->vacation_earned = number_format(max(0, ((int) round((float) $balance->vacation_earned * 1000) - $units)) / 1000, 3, '.', '');
                if ($credit->posted_bucket === 'sick') $balance->sick_earned = number_format(max(0, ((int) round((float) $balance->sick_earned * 1000) - $units)) / 1000, 3, '.', '');
                $balance->last_updated = now(); $balance->save();
            }
            $credit->revoked_reason = null; $credit->revoked_by = request()->user()->user_id; $credit->revoked_at = now();
            $credit->save();
            $credit->delete();
            AuditLogger::log('Leave credit revoked', "Revoked leave credit #{$credit->credits_id} for employee #{$credit->employee_id}");
        });

        return response()->json([
            'message' => 'Leave credit deleted successfully.'
        ]);
    }

    // VIEW DELETED CREDITS
    public function deleted()
    {
        $deletedCredits = LeaveCredit::onlyTrashed()->get();

        return response()->json([
            'data' => $deletedCredits
        ]);
    }

    // APPLY CREDIT
    public function apply(Request $request, $id)
    {
        $request->validate([
            'leave_type' => 'nullable|in:Vacation,Sick,Service',
            'days' => 'nullable|numeric|min:0',
            'split' => 'nullable|boolean',
            'vacation_days' => 'nullable|numeric|min:0',
            'sick_days' => 'nullable|numeric|min:0',
        ]);
        return DB::transaction(function () use ($request, $id) {
        $credit = LeaveCredit::whereKey($id)->lockForUpdate()->firstOrFail();

        // Prevent duplicate application
        if ($credit->status === 'Applied') {
            return response()->json([
                'message' => 'Leave credit has already been applied.'
            ], 400);
        }

        EmployeeRecord::whereKey($credit->employee_id)->lockForUpdate()->firstOrFail();
        $balance = LeaveBalance::withTrashed()->where('employee_id', $credit->employee_id)->lockForUpdate()->first();
        if ($balance?->trashed()) $balance->restore();
        $balance ??= new LeaveBalance(['employee_id' => $credit->employee_id]);
        foreach (['vacation_earned', 'sick_earned', 'vacation_balance', 'sick_balance', 'service_credits', 'used_leave'] as $field) $balance->{$field} ??= 0;

        $creditType = strtolower(trim($credit->credit_type));
        $availableDays = (float) $credit->equivalent_leave_days;

        $isServiceCredit = $creditType === 'service credits'
            || $creditType === 'service';

        $split = $request->boolean('split');
        $days = (float) ($request->input('days') ?? $availableDays);
        $targetType = $request->input('leave_type');

        $vacationDays = $split
            ? (float) ($request->input('vacation_days') ?? 0)
            : ($targetType === 'Vacation' ? $days : 0);

        $sickDays = $split
            ? (float) ($request->input('sick_days') ?? 0)
            : ($targetType === 'Sick' ? $days : 0);

        $appliedDays = $split
            ? $vacationDays + $sickDays
            : $days;

        $serviceDays = $isServiceCredit
            ? ($split || $targetType !== 'Service'
                ? max(0, $availableDays - $vacationDays - $sickDays)
                : min($days, $availableDays))
            : 0;

        if (
            $isServiceCredit
            && ($appliedDays <= 0 || $appliedDays > $availableDays)
        ) {
            return response()->json([
                'message' => 'Applied allocation cannot exceed the available service credit.'
            ], 422);
        }

        if ($creditType === 'vacation') {
            $balance->vacation_earned += $credit->equivalent_leave_days;
            $balance->vacation_balance += $credit->equivalent_leave_days;
        } elseif ($creditType === 'sick') {
            $balance->sick_earned += $credit->equivalent_leave_days;
            $balance->sick_balance += $credit->equivalent_leave_days;
        } elseif ($isServiceCredit) {
            $balance->service_credits =
                (float) ($balance->service_credits ?? 0) + $serviceDays;

            if ($vacationDays > 0) {
                $balance->vacation_earned += $vacationDays;
                $balance->vacation_balance += $vacationDays;
            }

            if ($sickDays > 0) {
                $balance->sick_earned += $sickDays;
                $balance->sick_balance += $sickDays;
            }
        } else {
            return response()->json([
                'message' => 'Invalid credit type.'
            ], 422);
        }

        $balance->last_updated = now();
        $balance->save();

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
        });
    }
}
