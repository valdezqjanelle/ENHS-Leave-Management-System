<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveSchoolYearController extends Controller
{
    private const FIELDS = ['vacation_earned', 'sick_earned', 'vacation_balance',
        'sick_balance', 'service_credits', 'used_leave'];

    public function index()
    {
        return response()->json(DB::table('leave_school_years')->orderByDesc('start_date')->get());
    }

    public function current()
    {
        return response()->json(['data' => DB::table('leave_school_years')
            ->orderByDesc('start_date')->first()]);
    }

    public function history($id)
    {
        $year = DB::table('leave_school_years')->where('id', $id)->first();
        abort_unless($year, 404, 'School year not found.');
        $entries = DB::table('leave_school_year_entries')->where('school_year_id', $id)
            ->orderBy('employee_name')->get()->map(function ($row) {
                $row->before_balances = json_decode($row->before_balances, true);
                $row->after_balances = json_decode($row->after_balances, true);
                return $row;
            });
        return response()->json(['school_year' => $year, 'entries' => $entries]);
    }

    private function input(Request $request, bool $apply = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:30', 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            // Explicit selection: no assumed SL grant while policy is unconfirmed.
            'sl_grant' => 'required|integer|in:0,15',
        ];
        if ($apply) {
            $rules += [
                'preview_token' => 'required|string',
                'employee_ids' => 'required|array|min:1',
                'employee_ids.*' => 'required|integer|distinct',
                'review_note' => 'required|string|min:10|max:2000',
                'confirmed' => 'accepted',
            ];
        }
        $data = $request->validate($rules);
        [$first, $second] = array_map('intval', explode('-', $data['name']));
        if ($second !== $first + 1 || Carbon::parse($data['start_date'])->year !== $first
            || Carbon::parse($data['end_date'])->year !== $second) {
            $this->fail('name', 'School-year label must match its dates, for example 2026-2027.');
        }
        if ($data['start_date'] > now('Asia/Manila')->toDateString()) {
            $this->fail('start_date', 'You can activate the school year only on or after its start date.');
        }
        return $data;
    }

    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    // All arithmetic is in thousandths of a day, preserving three-decimal balances.
    private function units($value): int
    {
        return (int) round((float) $value * 1000);
    }

    private function days(int $units): string
    {
        return number_format($units / 1000, 3, '.', '');
    }

    private function balances($balance): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $values[$field] = $this->days($this->units($balance?->{$field} ?? 0));
        }
        return $values;
    }

    private function previewData(array $data, bool $lock = false): array
    {
        $existing = DB::table('leave_school_years')->orderByDesc('start_date')->first();
        if ($existing && $data['start_date'] <= $existing->end_date) {
            $this->fail('start_date', 'This school year overlaps or precedes a processed school year.');
        }
        if (DB::table('leave_school_years')->where('name', $data['name'])->exists()) {
            $this->fail('name', 'This school year was already processed.');
        }
        // Do not deduct old-year applications from balances after the SL reset.
        $pending = LeaveApplication::whereRaw('LOWER(final_status) = ?', ['pending'])
            ->whereDate('start_date', '<', $data['start_date'])->count();
        $employees = EmployeeRecord::orderBy('employee_id');
        if ($lock) $employees->lockForUpdate();
        $employees = $employees->get();
        $balanceQuery = LeaveBalance::withTrashed()->orderBy('employee_id');
        if ($lock) $balanceQuery->lockForUpdate();
        $balances = $balanceQuery->get()->keyBy('employee_id');
        $rows = [];
        foreach ($employees as $employee) {
            $balance = $balances->get($employee->employee_id);
            $hired = $employee->date_hired?->format('Y-m-d');
            $review = !$hired || $hired > $data['start_date'] || ($balance && $balance->trashed());
            $before = $this->balances($balance);
            $after = $before;
            $after['vacation_balance'] = $this->days($this->units($before['vacation_balance']) + 15000);
            $after['sick_balance'] = $this->days(((int) $data['sl_grant']) * 1000);
            // Earned fields remain cumulative; used_leave remains lifetime usage.
            $after['vacation_earned'] = $this->days($this->units($before['vacation_earned']) + 15000);
            $after['sick_earned'] = $this->days($this->units($before['sick_earned']) + ((int) $data['sl_grant']) * 1000);
            $rows[] = [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->last_name . ', ' . $employee->first_name,
                'date_hired' => $hired,
                'needs_review' => $review,
                'can_process' => !$hired || $hired <= $data['end_date'],
                'before' => $before, 'after' => $after,
                'balance_deleted' => $balance && $balance->trashed(),
            ];
        }
        // Binds confirmation to the exact policy, roster and balances that were reviewed.
        $token = hash_hmac('sha256', json_encode([$data['name'], $data['start_date'],
            $data['end_date'], $data['sl_grant'], $pending, $rows]), config('app.key'));
        return ['rows' => $rows, 'pending_old_applications' => $pending, 'preview_token' => $token];
    }

    public function preview(Request $request)
    {
        return response()->json($this->previewData($this->input($request)));
    }

    public function activate(Request $request)
    {
        $data = $this->input($request, true);
        return DB::transaction(function () use ($data, $request) {
            // One persistent row serializes all school-year activations, including first use.
            DB::table('leave_year_control')->where('id', 1)->lockForUpdate()->first();
            $preview = $this->previewData($data, true);
            if ($preview['pending_old_applications'] > 0) {
                $this->fail('applications', 'Resolve pending applications dated before the new school year first.');
            }
            if (!hash_equals($preview['preview_token'], $data['preview_token'])) {
                $this->fail('preview_token', 'Balances or employees changed. Generate a new preview before confirming.');
            }
            $ids = array_map('intval', $data['employee_ids']);
            $validIds = array_column($preview['rows'], 'employee_id');
            if (array_diff($ids, $validIds)) $this->fail('employee_ids', 'Invalid employee selection.');
            $yearId = DB::table('leave_school_years')->insertGetId([
                'name' => $data['name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'],
                'vl_grant' => '15.000', 'sl_grant' => $data['sl_grant'],
                'processed_by' => $request->user()->user_id, 'processed_at' => now(),
            ]);
            foreach ($preview['rows'] as $row) {
                $selected = in_array((int) $row['employee_id'], $ids, true);
                if ($selected && (!$row['can_process'] || $row['balance_deleted'])) {
                    $this->fail('employee_ids', 'Restore archived balances or exclude employees hired after the year ends.');
                }
                if ($selected) {
                    $balance = LeaveBalance::firstOrNew(['employee_id' => $row['employee_id']]);
                    foreach ($row['after'] as $field => $value) $balance->{$field} = $value;
                    $balance->last_updated = now();
                    $balance->save();
                }
                DB::table('leave_school_year_entries')->insert([
                    'school_year_id' => $yearId, 'employee_id' => $row['employee_id'],
                    'employee_name' => $row['employee_name'], 'processed' => $selected,
                    'before_balances' => json_encode($row['before']),
                    'after_balances' => json_encode($selected ? $row['after'] : $row['before']),
                    'review_note' => $data['review_note'],
                ]);
            }
            AuditLogger::log('School year activated', "Processed {$data['name']}: VL +15; SL reset and grant {$data['sl_grant']}; " . count($ids) . ' employees.');
            return response()->json(['message' => 'School year processed successfully.', 'school_year_id' => $yearId], 201);
        });
    }
}
