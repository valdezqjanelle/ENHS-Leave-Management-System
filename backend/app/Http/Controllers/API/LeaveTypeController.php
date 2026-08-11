<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Support\AuditLogger;

class LeaveTypeController extends Controller
{
    // GET ALL LEAVE TYPES (for dropdown)
    public function index()
    {
        return response()->json(
            LeaveType::all()
        );
    }

    // CREATE LEAVE TYPE (ADMIN ONLY)
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_name' => 'required|string|unique:leave_types,leave_type_name',
            'description' => 'nullable|string',
        ]);

        $type = LeaveType::create($request->all());

        AuditLogger::log(
            'Leave type created',
            "Created leave type \"{$type->leave_type_name}\""
        );

        return response()->json([
            'message' => 'Leave type created successfully',
            'data' => $type
        ], 201);
    }

    // UPDATE LEAVE TYPE (ADMIN ONLY)
    public function update(Request $request, $id)
    {
        $type = LeaveType::findOrFail($id);

        $request->validate([
            'leave_type_name' => 'required|string|unique:leave_types,leave_type_name,' . $id . ',leave_type_id',
            'description' => 'nullable|string',
        ]);

        $type->update($request->all());

        AuditLogger::log(
            'Leave type updated',
            "Updated leave type \"{$type->leave_type_name}\""
        );

        return response()->json([
            'message' => 'Leave type updated successfully',
            'data' => $type
        ]);
    }

    // DELETE LEAVE TYPE (ADMIN ONLY)
    public function destroy($id)
    {
        $type = LeaveType::findOrFail($id);
        $typeName = $type->leave_type_name;
        $type->delete();

        AuditLogger::log(
            'Leave type deleted',
            "Deleted leave type \"{$typeName}\""
        );

        return response()->json([
            'message' => 'Leave type deleted successfully'
        ]);
    }
}
