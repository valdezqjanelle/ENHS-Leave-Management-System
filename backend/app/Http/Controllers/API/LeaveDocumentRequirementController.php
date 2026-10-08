<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveDocumentRequirement;
use App\Models\LeaveType;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class LeaveDocumentRequirementController extends Controller
{
    public function forApplication(Request $request, $leaveTypeId)
    {
        $validated = $request->validate([
            'number_of_days' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
        ]);
        LeaveType::findOrFail($leaveTypeId);
        $days = (int) ($validated['number_of_days'] ?? 0);
        $filedInAdvance = isset($validated['start_date'])
            && \Carbon\Carbon::parse($validated['start_date'])->isAfter(today());

        $requirements = LeaveDocumentRequirement::query()
            ->where('leave_type_id', $leaveTypeId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (LeaveDocumentRequirement $requirement) => [
                'id' => $requirement->id,
                'leave_type_id' => $requirement->leave_type_id,
                'document_name' => $requirement->document_name,
                'description' => $requirement->description,
                'is_required' => $requirement->is_required,
                'requirement_type' => $requirement->requirement_type,
                'condition_days' => $requirement->condition_days,
                'condition_filed_in_advance' => $requirement->condition_filed_in_advance,
                'is_active' => $requirement->is_active,
                'sort_order' => $requirement->sort_order,
                'required' => $requirement->isRequiredForDays($days, $filedInAdvance),
            ]);

        return response()->json($requirements);
    }

    public function index($leaveTypeId)
    {
        LeaveType::findOrFail($leaveTypeId);

        return LeaveDocumentRequirement::query()
            ->where('leave_type_id', $leaveTypeId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function store(Request $request, $leaveTypeId)
    {
        LeaveType::findOrFail($leaveTypeId);
        $validated = $this->validatedRequirement($request);
        $validated['leave_type_id'] = $leaveTypeId;

        $requirement = LeaveDocumentRequirement::create($validated);
        AuditLogger::log(
            'Leave document requirement created',
            "Added \"{$requirement->document_name}\" for leave type #{$leaveTypeId}"
        );

        return response()->json([
            'message' => 'Document requirement created successfully.',
            'data' => $requirement,
        ], 201);
    }

    public function update(Request $request, $leaveTypeId, $id)
    {
        $requirement = LeaveDocumentRequirement::query()
            ->where('leave_type_id', $leaveTypeId)
            ->findOrFail($id);

        $requirement->update($this->validatedRequirement($request));
        AuditLogger::log(
            'Leave document requirement updated',
            "Updated \"{$requirement->document_name}\" for leave type #{$leaveTypeId}"
        );

        return response()->json([
            'message' => 'Document requirement updated successfully.',
            'data' => $requirement->fresh(),
        ]);
    }

    private function validatedRequirement(Request $request): array
    {
        return $request->validate([
            'document_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_required' => 'required|boolean',
            'requirement_type' => 'required|in:always,conditional',
            'condition_days' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'condition_filed_in_advance' => 'required|boolean',
            'is_active' => 'required|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);
    }
}
