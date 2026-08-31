<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\LeaveType;
use App\Support\AuditLogger;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::query()
            ->orderBy('leave_type_name')
            ->get();

        return response()->json($leaveTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:leave_types,code',
            ],
            'leave_type_name' => [
                'required',
                'string',
                'max:255',
                'unique:leave_types,leave_type_name',
            ],
            'legal_basis' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['leave_type_name'] = trim(
            $validated['leave_type_name']
        );

        $type = LeaveType::create($validated);

        AuditLogger::log(
            'Leave type created',
            "Created leave type \"{$type->leave_type_name}\" ({$type->code})"
        );

        return response()->json([
            'message' => 'Leave type created successfully.',
            'data' => $type,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $type = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('leave_types', 'code')
                    ->ignore($type->leave_type_id, 'leave_type_id'),
            ],
            'leave_type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types', 'leave_type_name')
                    ->ignore($type->leave_type_id, 'leave_type_id'),
            ],
            'legal_basis' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['leave_type_name'] = trim(
            $validated['leave_type_name']
        );

        $type->update($validated);

        AuditLogger::log(
            'Leave type updated',
            "Updated leave type \"{$type->leave_type_name}\" ({$type->code})"
        );

        return response()->json([
            'message' => 'Leave type updated successfully.',
            'data' => $type->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $type = LeaveType::findOrFail($id);

        if ($type->leaveApplications()->exists()) {
            return response()->json([
                'message' =>
                    'This leave type cannot be deleted because it is already used in a leave application.',
            ], 422);
        }

        $typeName = $type->leave_type_name;
        $typeCode = $type->code;

        $type->delete();

        AuditLogger::log(
            'Leave type deleted',
            "Deleted leave type \"{$typeName}\" ({$typeCode})"
        );

        return response()->json([
            'message' => 'Leave type deleted successfully.',
        ]);
    }
}