<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {

        $totalEmployees = EmployeeRecord::count();

        $totalApplications = LeaveApplication::count();

        $pendingLeaves = LeaveApplication::where(
            'final_status',
            'pending'
        )->count();


        $approvedLeaves = LeaveApplication::where(
            'final_status',
            'approved'
        )->count();


        $disapprovedLeaves = LeaveApplication::where(
            'final_status',
            'disapproved'
        )->count();


        $totalLeaveTypes = LeaveType::count();



        /*
        |--------------------------------------------------------------------------
        | LEAVE STATUS CHART
        |--------------------------------------------------------------------------
        */

        $statusChart = [
            "approved" => $approvedLeaves,
            "pending" => $pendingLeaves,
            "disapproved" => $disapprovedLeaves
        ];



        /*
        |--------------------------------------------------------------------------
        | LEAVE USAGE BY TYPE
        |--------------------------------------------------------------------------
        */

        $leaveByType = LeaveApplication::with('leaveType')
            ->select(
                'leave_type_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('leave_type_id')
            ->get()
            ->map(function($leave){

                return [
                    "name" =>
                        $leave->leaveType->leave_type_name,

                    "count" =>
                        $leave->total
                ];

            });



        /*
        |--------------------------------------------------------------------------
        | LEAVE SUMMARY BY DEPARTMENT
        |--------------------------------------------------------------------------
        */

        $leaveByDepartment = LeaveApplication::with('employee')
            ->select(
                'employee_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('employee_id')
            ->get()
            ->groupBy(function($leave){

                return $leave->employee->department;

            })
            ->map(function($department, $name){

                return [
                    "department" => $name,

                    "count" =>
                        $department->sum('total')
                ];

            })
            ->values();



        /*
        |--------------------------------------------------------------------------
        | RECENT APPLICATIONS
        |--------------------------------------------------------------------------
        */

        $recentApplications = LeaveApplication::with([
                'employee',
                'leaveType'
            ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($leave){

                return [

                    "employee" =>
                        $leave->employee->first_name
                        ." "
                        .$leave->employee->last_name,


                    "leave_type" =>
                        $leave->leaveType->leave_type_name,


                    "status" =>
                        $leave->final_status,


                    "date" =>
                        $leave->created_at
                            ->format('M d, Y')

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE CATEGORY
        |--------------------------------------------------------------------------
        */

        $employeeCategory = EmployeeRecord::select(
                'employee_category',
                DB::raw('count(*) as count')
            )
            ->groupBy('employee_category')
            ->get();



        /*
        |--------------------------------------------------------------------------
        | LEAVE TREND
        |--------------------------------------------------------------------------
        */

        $leaveTrend = LeaveApplication::select(

                DB::raw(
                    'EXTRACT(MONTH FROM created_at) as month'
                ),

                DB::raw(
                    'count(*) as total'
                )

            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();



        return response()->json([


            "summary" => [

                "totalEmployees" =>
                    $totalEmployees,

                "totalApplications" =>
                    $totalApplications,

                "pendingLeaves" =>
                    $pendingLeaves,

                "approvedLeaves" =>
                    $approvedLeaves,

                "disapprovedLeaves" =>
                    $disapprovedLeaves,

                "totalLeaveTypes" =>
                    $totalLeaveTypes

            ],



            "statusChart" =>
                $statusChart,



            "leaveByType" =>
                $leaveByType,



            "leaveByDepartment" =>
                $leaveByDepartment,



            "recentApplications" =>
                $recentApplications,



            "employeeCategory" =>
                $employeeCategory,



            "leaveTrend" =>
                $leaveTrend


        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE DASHBOARD
    |--------------------------------------------------------------------------
    | NOTE: this assumes EmployeeRecord has a `user_id` column linking it to
    | the logged-in user. If your schema links them differently, change the
    | lookup below (the `$employee = ...` line) to match.
    */

    public function employeeIndex(Request $request)
    {

        $employee = EmployeeRecord::where(
            'user_id',
            auth()->id()
        )->firstOrFail();


        $totalApplications = LeaveApplication::where(
            'employee_id',
            $employee->id
        )->count();


        $pendingLeaves = LeaveApplication::where(
                'employee_id',
                $employee->id
            )
            ->where('final_status', 'pending')
            ->count();


        $approvedLeaves = LeaveApplication::where(
                'employee_id',
                $employee->id
            )
            ->where('final_status', 'approved')
            ->count();


        $disapprovedLeaves = LeaveApplication::where(
                'employee_id',
                $employee->id
            )
            ->where('final_status', 'disapproved')
            ->count();



        /*
        |--------------------------------------------------------------------------
        | LEAVE STATUS CHART
        |--------------------------------------------------------------------------
        */

        $statusChart = [
            "approved" => $approvedLeaves,
            "pending" => $pendingLeaves,
            "disapproved" => $disapprovedLeaves
        ];



        /*
        |--------------------------------------------------------------------------
        | MY APPLICATIONS
        |--------------------------------------------------------------------------
        */

        $myApplications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->id,

                    "leave_type" =>
                        $leave->leaveType->leave_type_name,

                    "status" =>
                        $leave->final_status,

                    "days" =>
                        $leave->days,

                    "start_date" =>
                        optional($leave->start_date)->format('M d, Y'),

                    "end_date" =>
                        optional($leave->end_date)->format('M d, Y')

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | UPCOMING (APPROVED, NOT YET STARTED) LEAVES
        |--------------------------------------------------------------------------
        */

        $upcomingLeaves = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('final_status', 'approved')
            ->where('start_date', '>=', Carbon::now())
            ->orderBy('start_date')
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->id,

                    "leave_type" =>
                        $leave->leaveType->leave_type_name,

                    "start_date" =>
                        optional($leave->start_date)->format('M d, Y'),

                    "end_date" =>
                        optional($leave->end_date)->format('M d, Y'),

                    "days" =>
                        $leave->days

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | LEAVE USAGE BY TYPE (THIS EMPLOYEE ONLY)
        |--------------------------------------------------------------------------
        */

        $leaveByType = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->select(
                'leave_type_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('leave_type_id')
            ->get()
            ->map(function($leave){

                return [
                    "name" =>
                        $leave->leaveType->leave_type_name,

                    "count" =>
                        $leave->total
                ];

            });



        /*
        |--------------------------------------------------------------------------
        | RECENT ACTIVITIES (THIS EMPLOYEE ONLY)
        |--------------------------------------------------------------------------
        */

        $recentActivities = LeaveApplication::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->id,

                    "message" =>
                        "Leave application ({$leave->final_status})",

                    "time" =>
                        $leave->created_at->diffForHumans()

                ];

            });



        return response()->json([


            "summary" => [

                "totalApplications" =>
                    $totalApplications,

                "pendingLeaves" =>
                    $pendingLeaves,

                "approvedLeaves" =>
                    $approvedLeaves,

                "disapprovedLeaves" =>
                    $disapprovedLeaves

            ],



            "employee" => [

                "name" =>
                    $employee->first_name
                    ." "
                    .$employee->last_name,

                "email" =>
                    $employee->email ?? '',

                "department" =>
                    $employee->department,

                "position" =>
                    $employee->position

            ],



            "myApplications" =>
                $myApplications,



            "upcomingLeaves" =>
                $upcomingLeaves,



            "statusChart" =>
                $statusChart,



            "leaveByType" =>
                $leaveByType,



            "recentActivities" =>
                $recentActivities


        ]);

    }
}