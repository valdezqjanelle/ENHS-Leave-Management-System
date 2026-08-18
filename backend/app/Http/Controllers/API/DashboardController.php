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

        /*
        |--------------------------------------------------------------------------
        | SUMMARY CARDS
        |--------------------------------------------------------------------------
        */

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

                    "id" =>
                        $leave->leave_id,

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



        /*
        |--------------------------------------------------------------------------
        | PENDING REQUESTS (For Admin Review)
        |--------------------------------------------------------------------------
        */

        $pendingRequests = LeaveApplication::with([
                'employee',
                'leaveType'
            ])
            ->where('final_status', 'pending')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->leave_id,

                    "employee" =>
                        $leave->employee->first_name
                        ." "
                        .$leave->employee->last_name,


                    "leave_type" =>
                        $leave->leaveType->leave_type_name,


                    "days" =>
                        $leave->number_of_days,

                    "start_date" =>
                        $leave->start_date
                            ->format('M d, Y'),

                    "end_date" =>
                        $leave->end_date
                            ->format('M d, Y'),


                    "status" =>
                        $leave->final_status

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | RECENT ACTIVITIES
        |--------------------------------------------------------------------------
        */

        $recentActivities = LeaveApplication::with([
                'employee',
                'leaveType'
            ])
            ->latest()
            ->take(8)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->leave_id,

                    "message" =>
                        $leave->employee->first_name
                        ." "
                        .$leave->employee->last_name
                        ." submitted a "
                        .$leave->leaveType->leave_type_name
                        ." leave request",

                    "time" =>
                        $leave->created_at
                            ->diffForHumans()

                ];

            });



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
                $leaveTrend,



            "pendingRequests" =>
                $pendingRequests,



            "recentActivities" =>
                $recentActivities


        ]);

    }

    public function employeeDashboard(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'error' => 'Employee record not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE LEAVE STATISTICS
        |--------------------------------------------------------------------------
        */

        $totalApplications = LeaveApplication::where('employee_id', $employee->employee_id)->count();

        $pendingLeaves = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('final_status', 'pending')
            ->count();

        $approvedLeaves = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('final_status', 'approved')
            ->count();

        $disapprovedLeaves = LeaveApplication::where('employee_id', $employee->employee_id)
            ->where('final_status', 'disapproved')
            ->count();



        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE STATUS CHART
        |--------------------------------------------------------------------------
        */

        $statusChart = [
            "approved" => $approvedLeaves,
            "pending" => $pendingLeaves,
            "disapproved" => $disapprovedLeaves
        ];



        /*
        |--------------------------------------------------------------------------
        | MY LEAVE APPLICATIONS
        |--------------------------------------------------------------------------
        */

        $myApplications = LeaveApplication::with([
                'leaveType'
            ])
            ->where('employee_id', $employee->employee_id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->leave_id,

                    "leave_type" =>
                        $leave->leaveType->leave_type_name,

                    "start_date" =>
                        $leave->start_date
                            ->format('M d, Y'),

                    "end_date" =>
                        $leave->end_date
                            ->format('M d, Y'),

                    "days" =>
                        $leave->number_of_days,

                    "status" =>
                        $leave->final_status,

                    "date_filed" =>
                        $leave->created_at
                            ->format('M d, Y')

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | LEAVE BY TYPE (Employee's leave breakdown)
        |--------------------------------------------------------------------------
        */

        $leaveByType = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->employee_id)
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
        | RECENT LEAVE ACTIVITIES
        |--------------------------------------------------------------------------
        */

        $recentActivities = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->employee_id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->leave_id,

                    "message" =>
                        "Your "
                        .$leave->leaveType->leave_type_name
                        ." leave request is "
                        .ucfirst($leave->final_status),

                    "leave_type" =>
                        $leave->leaveType->leave_type_name,

                    "status" =>
                        $leave->final_status,

                    "time" =>
                        $leave->updated_at
                            ->diffForHumans()

                ];

            });



        /*
        |--------------------------------------------------------------------------
        | UPCOMING LEAVES (Approved leaves)
        |--------------------------------------------------------------------------
        */

        $upcomingLeaves = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->employee_id)
            ->where('final_status', 'approved')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function($leave){

                return [

                    "id" =>
                        $leave->leave_id,

                    "leave_type" =>
                        $leave->leaveType->leave_type_name,

                    "start_date" =>
                        $leave->start_date
                            ->format('M d, Y'),

                    "end_date" =>
                        $leave->end_date
                            ->format('M d, Y'),

                    "days" =>
                        $leave->number_of_days

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
                    $employee->first_name . " " . $employee->last_name,

                "email" =>
                    $user->email,

                "department" =>
                    $employee->department,

                "position" =>
                    $employee->position

            ],

            "statusChart" =>
                $statusChart,

            "leaveByType" =>
                $leaveByType,

            "myApplications" =>
                $myApplications,

            "recentActivities" =>
                $recentActivities,

            "upcomingLeaves" =>
                $upcomingLeaves

        ]);

    }
}

