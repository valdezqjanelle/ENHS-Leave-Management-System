<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\TeachingPersonnelRecord;
use Illuminate\Http\Request;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class TeachingPersonnelRecordController extends Controller
{
 
    public function index()
    {
        $records = TeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
            'assignments.gradeLevel',
            'assignments.section',
            'assignments.subject',
        ])
        ->latest()
        ->get();

        return response()->json($records);
    }

  
    public function show($id)
    {
        $record = TeachingPersonnelRecord::with([
            'employee.user',
            'employee.position',
            'employee.department',
            'employee.supervisor',
            'assignments.gradeLevel',
            'assignments.section',
            'assignments.subject',
        ])->findOrFail($id);

        return response()->json($record);
    }


    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'subject_specialization' => 'required|string|max:255',
            'grade_level_handled' => 'required|string|max:255',
            'advisory_class' => 'nullable|string|max:255',
            'teaching_load' => 'nullable|string',
            'teaching_hours' => 'nullable|numeric|min:0|max:99.99',
            'assignments' => 'nullable|array',
            'assignments.*.grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'assignments.*.section_id' => 'nullable|exists:school_sections,section_id',
            'assignments.*.subject_id' => 'required|exists:teaching_subjects,subject_id',
            'assignments.*.school_year' => ['required', 'regex:/^\\d{4}-\\d{4}$/'],
            'assignments.*.is_advisory' => 'nullable|boolean',
            'assignments.*.teaching_hours' => 'nullable|numeric|min:0|max:99.99',
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

        $this->syncAssignments($record, $request->input('assignments', []));

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
                'assignments.gradeLevel', 'assignments.section', 'assignments.subject',
            ]),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $record = TeachingPersonnelRecord::findOrFail($id);

        $request->validate([
            'subject_specialization' => 'required|string|max:255',
            'grade_level_handled' => 'required|string|max:255',
            'advisory_class' => 'nullable|string|max:255',
            'teaching_load' => 'nullable|string',
            'teaching_hours' => 'nullable|numeric|min:0|max:99.99',
            'assignments' => 'nullable|array',
            'assignments.*.grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'assignments.*.section_id' => 'nullable|exists:school_sections,section_id',
            'assignments.*.subject_id' => 'required|exists:teaching_subjects,subject_id',
            'assignments.*.school_year' => ['required', 'regex:/^\\d{4}-\\d{4}$/'],
            'assignments.*.is_advisory' => 'nullable|boolean',
            'assignments.*.teaching_hours' => 'nullable|numeric|min:0|max:99.99',
        ]);

        $record->update([
            'subject_specialization' => $request->subject_specialization,
            'grade_level_handled' => $request->grade_level_handled,
            'advisory_class' => $request->advisory_class,
            'teaching_load' => $request->teaching_load,
            'teaching_hours' => $request->teaching_hours,
        ]);

        if ($request->has('assignments')) {
            $this->syncAssignments($record, $request->input('assignments', []));
        }

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
                'assignments.gradeLevel', 'assignments.section', 'assignments.subject',
            ]),
        ]);
    }


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

    private function syncAssignments(TeachingPersonnelRecord $record, array $assignments): void
    {
        DB::transaction(function () use ($record, $assignments) {
            $record->assignments()->delete();
            foreach ($assignments as $assignment) {
                if (!empty($assignment['section_id'])) {
                    $valid = \App\Models\SchoolSection::where('section_id', $assignment['section_id'])
                        ->where('grade_level_id', $assignment['grade_level_id'])->exists();
                    abort_unless($valid, 422, 'The selected section does not belong to the selected grade level.');
                }
                $record->assignments()->create($assignment);
            }
        });
    }
}
