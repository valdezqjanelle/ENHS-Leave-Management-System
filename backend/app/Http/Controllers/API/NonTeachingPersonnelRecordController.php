<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\NonTeachingPersonnelRecord;
use Illuminate\Http\Request;
use App\Support\AuditLogger;

class NonTeachingPersonnelRecordController extends Controller
{
    public function index()
    {
        $records = NonTeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
        ])
        ->latest()
        ->get();

        return response()->json($records);
    }

    public function show($id)
    {
        $record = NonTeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
        ])->findOrFail($id);

        return response()->json($record);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'office_assignment' => 'required|string|max:255',
            'job_assignment' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
        ]);

        $employee = EmployeeRecord::findOrFail($request->employee_id);

        if ($employee->personnel_type !== 'Non-Teaching') {
            return response()->json([
                'message' => 'Only non-teaching personnel can have a non-teaching personnel record.'
            ], 422);
        }

        if (NonTeachingPersonnelRecord::where(
            'employee_id',
            $request->employee_id
        )->exists()) {
            return response()->json([
                'message' => 'This employee already has a non-teaching personnel record.'
            ], 422);
        }

        $record = NonTeachingPersonnelRecord::create([
            'employee_id' => $request->employee_id,
            'office_assignment' => $request->office_assignment,
            'job_assignment' => $request->job_assignment,
            'job_description' => $request->job_description,
        ]);

        AuditLogger::log(
            'Non-teaching personnel record created',
            "Created non-teaching personnel record for {$employee->first_name} {$employee->last_name}"
        );

        return response()->json([
            'message' => 'Non-teaching personnel record created successfully.',
            'record' => $record->load([
                'employee.user',
                'employee.position',
                'employee.department',
                'employee.supervisor',
            ]),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $record = NonTeachingPersonnelRecord::findOrFail($id);

        $request->validate([
            'office_assignment' => 'required|string|max:255',
            'job_assignment' => 'nullable|string|max:255',
            'job_description' => 'nullable|string',
        ]);

        $record->update([
            'office_assignment' => $request->office_assignment,
            'job_assignment' => $request->job_assignment,
            'job_description' => $request->job_description,
        ]);

        AuditLogger::log(
            'Non-teaching personnel record updated',
            "Updated non-teaching personnel record for {$record->employee->first_name} {$record->employee->last_name}"
        );

        return response()->json([
            'message' => 'Non-teaching personnel record updated successfully.',
            'record' => $record->fresh()->load([
                'employee.user',
                'employee.position',
                'employee.department',
                'employee.supervisor',
            ]),
        ]);
    }

    public function destroy($id)
    {
        $record = NonTeachingPersonnelRecord::findOrFail($id);

        $employeeName = $record->employee->first_name . ' ' .
                        $record->employee->last_name;

        $record->delete();

        AuditLogger::log(
            'Non-teaching personnel record deleted',
            "Deleted non-teaching personnel record for {$employeeName}"
        );

        return response()->json([
            'message' => 'Non-teaching personnel record deleted successfully.'
        ]);
    }
}