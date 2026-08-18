<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AttendanceRecord;
use App\Models\EmployeeRecord;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN: RECORD ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_records,employee_id',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|string'
        ]);

        $attendance = AttendanceRecord::create([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,

        ]);

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance->load('employee')
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: VIEW ALL ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return AttendanceRecord::with('employee')
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: VIEW SINGLE ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return AttendanceRecord::with('employee')
            ->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: UPDATE ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $attendance = AttendanceRecord::findOrFail($id);

        $attendance->update([
            'attendance_date' => $request->attendance_date ?? $attendance->attendance_date,
            'time_in' => $request->time_in ?? $attendance->time_in,
            'time_out' => $request->time_out ?? $attendance->time_out,
            'status' => $request->status ?? $attendance->status,

        ]);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'data' => $attendance->load('employee')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE: VIEW OWN ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function myAttendance(Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        return AttendanceRecord::where(
            'employee_id',
            $employee->employee_id
        )
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE: VIEW SINGLE OWN ATTENDANCE
    |--------------------------------------------------------------------------
    */
    public function myAttendanceRecord($id, Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        return AttendanceRecord::where(
            'employee_id',
            $employee->employee_id
        )
            ->where('attendance_id', $id)
            ->firstOrFail();
    }
}
