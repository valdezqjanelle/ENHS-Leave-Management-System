<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\TeachingPersonnelRecord;
use Illuminate\Http\Request;
use App\Support\AuditLogger;

class TeachingPersonnelRecordController extends Controller
{
    /**
     * Display all teaching personnel records.
     */
    public function index()
    {
        $records = TeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
        ])
        ->latest()
        ->get();

        return response()->json($records);
    }

    /**
     * Display a specific teaching personnel record.
     */
    public function show($id)
    {
        $record = TeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
        ])->findOrFail($id);

        return response()->json($record);
    }

    /**
     * Store a new teaching personnel record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'subject_specialization' => 'required|string|max:255',
            'grade_level_handled' => 'required|string|max:255',
            'advisory_class' => 'nullable|string|max:255',
            'teaching_load' => 'nullable|string',
            'teaching_hours' => 'nullable|numeric|min:0|max:99.99',
        ]);

        $employee = EmployeeRecord::findOrFail($request->employee_id);

        if ($employee->personnel_type !== 'Teaching') {
            return response()->json([
                'message' => 'Only teaching personnel can have a teaching personnel record.'
            ], 422);
        }

        if (TeachingPersonnelRecord::where(
            'employee_id',
            $request->employee_id
        )->exists()) {
            return response()->json([
                'message' => 'This employee already has a teaching personnel record.'
            ], 422);
        }

        $record = TeachingPersonnelRecord::create([
            'employee_id' => $request->employee_id,
            'subject_specialization' => $request->subject_specialization,
            'grade_level_handled' => $request->grade_level_handled,
            'advisory_class' => $request->advisory_class,
            'teaching_load' => $request->teaching_load,
            'teaching_hours' => $request->teaching_hours,
        ]);

        AuditLogger::log(
            'Teaching personnel record created',
            "Created teaching personnel record for {$employee->first_name} {$employee->last_name}"
        );

        return response()->json([
            'message' => 'Teaching personnel record created successfully.',
            'record' => $record->load([
                'employee.user',
                'employee.position',
                'employee.department',
                'employee.supervisor',
            ]),
        ], 201);
    }

    /**
     * Update a teaching personnel record.
     */
    public function update(Request $request, $id)
    {
        $record = TeachingPersonnelRecord::findOrFail($id);

        $request->validate([
            'subject_specialization' => 'required|string|max:255',
            'grade_level_handled' => 'required|string|max:255',
            'advisory_class' => 'nullable|string|max:255',
            'teaching_load' => 'nullable|string',
            'teaching_hours' => 'nullable|numeric|min:0|max:99.99',
        ]);

        $record->update([
            'subject_specialization' => $request->subject_specialization,
            'grade_level_handled' => $request->grade_level_handled,
            'advisory_class' => $request->advisory_class,
            'teaching_load' => $request->teaching_load,
            'teaching_hours' => $request->teaching_hours,
        ]);

        AuditLogger::log(
            'Teaching personnel record updated',
            "Updated teaching personnel record for {$record->employee->first_name} {$record->employee->last_name}"
        );

        return response()->json([
            'message' => 'Teaching personnel record updated successfully.',
            'record' => $record->fresh()->load([
                'employee.user',
                'employee.position',
                'employee.department',
                'employee.supervisor',
            ]),
        ]);
    }

    /**
     * Delete a teaching personnel record.
     */
    public function destroy($id)
    {
        $record = TeachingPersonnelRecord::findOrFail($id);

        $employeeName = $record->employee->first_name . ' ' .
                        $record->employee->last_name;

        $record->delete();

        AuditLogger::log(
            'Teaching personnel record deleted',
            "Deleted teaching personnel record for {$employeeName}"
        );

        return response()->json([
            'message' => 'Teaching personnel record deleted successfully.'
        ]);
    }
}