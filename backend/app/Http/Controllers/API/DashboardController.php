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




        $statusChart = [
            "approved" => $approvedLeaves,
            "pending" => $pendingLeaves,
            "disapproved" => $disapprovedLeaves
        ];





        $leaveByType = LeaveApplication::with('leaveType')
            ->select(
                'leave_type_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('leave_type_id')
            ->get()
            ->map(function ($leave) {

                return [
                    "name" =>
                    $leave->leaveType->leave_type_name,

                    "count" =>
                    $leave->total
                ];
            });



        $leaveByDepartment = LeaveApplication::with('employee')
            ->select(
                'employee_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('employee_id')
            ->get()
            ->groupBy(function ($leave) {

                return $leave->employee->department;
            })
            ->map(function ($department, $name) {

                return [
                    "department" => $name,

                    "count" =>
                    $department->sum('total')
                ];
            })
            ->values();




        $recentApplications = LeaveApplication::with([
            'employee',
            'leaveType'
        ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($leave) {

                return [

                    "employee" =>
                    $leave->employee->first_name
                        . " "
                        . $leave->employee->last_name,


                    "leave_type" =>
                    $leave->leaveType->leave_type_name,


                    "status" =>
                    $leave->final_status,


                    "date" =>
                    $leave->created_at
                        ->format('M d, Y')

                ];
            });




        $employeeCategory = EmployeeRecord::select(
            'employee_category',
            DB::raw('count(*) as count')
        )
            ->groupBy('employee_category')
            ->get();


        $leaveTrend = LeaveApplication::select(

            DB::raw(
                'MONTH(created_at) as month'
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
}
