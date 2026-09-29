<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveCredit;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveCreditController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'credit_type' => 'required|in:Service,Vacation,Sick',
            'activity_name' => 'required|string|max:255',
            'hours_rendered' => 'required|numeric|min:0',
            'equivalent_leave_days' => 'required|numeric|gt:0',
        ]);

        $credit = DB::transaction(function () use ($data, $request) {
            EmployeeRecord::whereKey($data['employee_id'])->lockForUpdate()->firstOrFail();
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $data['employee_id']],
                [
                    'vacation_earned' => 0,
                    'sick_earned' => 0,
                    'vacation_balance' => 0,
                    'sick_balance' => 0,
                    'service_credits' => 0,
                    'used_leave' => 0
                ]
            );
            $balance = LeaveBalance::whereKey($balance->getKey())->lockForUpdate()->firstOrFail();
            $days = (float) $data['equivalent_leave_days'];
            $bucket = match ($data['credit_type']) {
                'Service' => 'service_credits',
                'Vacation' => 'vacation_balance',
                'Sick' => 'sick_balance',
            };
            $balance->{$bucket} = round((float) $balance->{$bucket} + $days, 2);
            if ($data['credit_type'] !== 'Service') {
                $earned = strtolower($data['credit_type']) . '_earned';
                $balance->{$earned} = round((float) $balance->{$earned} + $days, 2);
            }
            $balance->last_updated = now();
            $balance->save();

            $credit = LeaveCredit::create([
                ...$data,
                'status' => 'Applied', // Existing database enum: this record is already in the balance.
                'posted_bucket' => $bucket,
                'posted_days' => $days,
                'date_recorded' => today(),
                'recorded_by' => $request->user()->user_id,
            ]);
            AuditLogger::log(
                'Leave credit posted',
                "Posted {$days} {$data['credit_type']} day(s) for employee #{$data['employee_id']} (credit #{$credit->credits_id})"
            );
            return $credit;
        });

        return response()->json([
            'message' => 'Credit recorded and balance updated.',
            'data' => $credit->load('employee'),
        ], 201);
    }

    public function index(Request $request)
    {
        $query = $request->query('view') === 'revoked'
            ? LeaveCredit::onlyTrashed()
            : LeaveCredit::query();
        return response()->json($query->with('employee')->orderBy('credits_id', 'desc')->get());
    }

    public function show($employee_id)
    {
        return response()->json([
            'data' => LeaveCredit::where('employee_id', $employee_id)->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Credit entries cannot be edited after posting. Revoke, then create a corrected entry.',
        ], 409);
    }

    public function destroy(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'required|string|min:3|max:500']);
        return DB::transaction(function () use ($data, $request, $id) {
            $record = LeaveCredit::findOrFail($id);
            EmployeeRecord::whereKey($record->employee_id)->lockForUpdate()->firstOrFail();
            $credit = LeaveCredit::whereKey($id)->lockForUpdate()->firstOrFail();

            // Older rows may have been posted by a second browser request or split
            // between buckets. There is no reliable reversal amount for those rows.
            if ($credit->status !== 'Applied' || !$credit->posted_bucket || !$credit->posted_days) {
                return response()->json([
                    'message' => 'This older credit has no verified posting details. Reconcile it manually before revocation.',
                ], 409);
            }

            // Without a per-credit consumption ledger, avoid reversing a credit
            // after leave approval may have drawn from its balance bucket.
            $laterApproval = LeaveApplication::where('employee_id', $credit->employee_id)
                ->where('final_status', 'approved')
                ->where('updated_at', '>=', $credit->created_at)
                ->exists();
            if ($laterApproval) {
                return response()->json([
                    'message' => 'An approved leave was updated after this credit was posted. Review its deduction before revoking.',
                ], 409);
            }

            $balance = LeaveBalance::where('employee_id', $credit->employee_id)
                ->lockForUpdate()->first();
            $bucket = $credit->posted_bucket;
            $days = (float) $credit->posted_days;
            if (
                !in_array($bucket, ['service_credits', 'vacation_balance', 'sick_balance'], true) ||
                !$balance || (float) $balance->{$bucket} + 0.00001 < $days
            ) {
                return response()->json([
                    'message' => 'Available balance is insufficient for this reversal. Review the balance and leave history.',
                ], 409);
            }
            $earned = match ($bucket) {
                'vacation_balance' => 'vacation_earned',
                'sick_balance' => 'sick_earned',
                default => null,
            };
            if ($earned && (float) $balance->{$earned} + 0.00001 < $days) {
                return response()->json([
                    'message' => 'The earned balance has changed. Reconcile this record before revoking.',
                ], 409);
            }
            $balance->{$bucket} = round((float) $balance->{$bucket} - $days, 2);
            if ($earned) {
                $balance->{$earned} = round((float) $balance->{$earned} - $days, 2);
            }
            $balance->last_updated = now();
            $balance->save();

            $credit->revoked_reason = $data['reason'];
            $credit->revoked_by = $request->user()->user_id;
            $credit->revoked_at = now();
            $credit->save();
            $credit->delete(); // Preserves it for Revoked History.
            AuditLogger::log(
                'Leave credit revoked',
                "Reversed credit #{$credit->credits_id} for employee #{$credit->employee_id}: {$data['reason']}"
            );

            return response()->json(['message' => 'Credit revoked and balance reversed.']);
        });
    }

    public function deleted()
    {
        return response()->json(['data' => LeaveCredit::onlyTrashed()->with('employee')->get()]);
    }

    public function apply(Request $request, $id)
    {
        return response()->json([
            'message' => 'Credits are now posted when they are recorded. Apply Credit is no longer available.',
        ], 409);
    }
}
