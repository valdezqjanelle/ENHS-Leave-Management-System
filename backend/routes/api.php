<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\LeaveCreditController;
use App\Http\Controllers\API\LeaveBalanceController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuditLogController;
use App\Http\Controllers\API\LeaveSettingController;
use App\Http\Controllers\ApprovalSettingController;
use App\Http\Controllers\API\SystemSettingController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });


    /*
    |--------------------------------------------------------------------------
    | SETTINGS - READ ACCESS
    |--------------------------------------------------------------------------
    */

    Route::get('/leave-settings', [LeaveSettingController::class, 'index']);
    Route::get('/approval-settings', [ApprovalSettingController::class, 'index']);
    Route::get('/system-settings', [SystemSettingController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:employee')->group(function () {

        // Dashboard
        Route::get('/employee/dashboard', [DashboardController::class, 'employeeIndex']);

        // Profile
        Route::get('/my-profile', [EmployeeController::class, 'myProfile']);
        Route::get('/profile', [EmployeeController::class, 'myProfile']);
        Route::put('/profile', [EmployeeController::class, 'updateProfile']);
        Route::put('/profile/email', [EmployeeController::class, 'updateEmail']);
        Route::put('/profile/password', [EmployeeController::class, 'updatePassword']);
        Route::put('/profile/phone', [EmployeeController::class, 'updatePhone']);

        // Leave Applications
        Route::post('/leave-applications', [LeaveController::class, 'store']);
        Route::get('/my-leave-applications', [LeaveController::class, 'myLeaves']);
        Route::get('/my-leave-applications/{id}', [LeaveController::class, 'myLeave']);

        // Leave Balance
        Route::get('/my-leave-balance', [LeaveBalanceController::class, 'myBalance']);

        // Attendance
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance']);
        Route::get('/my-attendance/{id}', [AttendanceController::class, 'myAttendanceRecord']);
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        // Dashboard
        Route::get('/admin/dashboard', [DashboardController::class, 'index']);


        // Employee Management
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::put('/employees/{id}', [EmployeeController::class, 'update']);


        // Leave Management
        Route::get('/leave-applications', [LeaveController::class, 'index']);
        Route::get('/leave-applications/{id}', [LeaveController::class, 'show']);
        Route::put('/leave-applications/{id}', [LeaveController::class, 'updateStatus']);
        Route::get('/leave-applications/{id}/pdf', [LeaveController::class, 'downloadPdf']);

        Route::get('/leaves/{leave_id}/attachments/{attachment_id}', [LeaveController::class, 'downloadAttachment']);


        // Leave Credits
        Route::post('/leave-credits', [LeaveCreditController::class, 'store']);
        Route::get('/leave-credits', [LeaveCreditController::class, 'index']);
        Route::get('/leave-credits/{employee_id}', [LeaveCreditController::class, 'show']);
        Route::put('/leave-credits/{id}', [LeaveCreditController::class, 'update']);
        Route::post('/leave-credits/{id}/apply', [LeaveCreditController::class, 'apply']);


        // Leave Balances
        Route::get('/leave-balances', [LeaveBalanceController::class, 'index']);
        Route::get('/leave-balances/{employee_id}', [LeaveBalanceController::class, 'show']);
        Route::put('/leave-balances/{employee_id}', [LeaveBalanceController::class, 'update']);


        // Attendance
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);


        // Reports
        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/leave-summary', [ReportController::class, 'leaveSummary']);
        Route::get('/reports/leave-credits', [ReportController::class, 'leaveCredits']);
        Route::post('/reports/attendance', [ReportController::class, 'generateAttendanceReport']);
        Route::get('/reports/download/{id}', [ReportController::class, 'download']);
        Route::get('/reports/employees', [ReportController::class, 'employeeReport']);


        // Admin Profile
        Route::get('/admin/profile', [AdminController::class, 'profile']);
        Route::put('/admin/profile', [AdminController::class, 'updateProfile']);
        Route::put('/admin/email', [AdminController::class, 'updateEmail']);
        Route::put('/admin/password', [AdminController::class, 'updatePassword']);


        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/actions', [AuditLogController::class, 'actions']);


        // Settings - Update
        Route::put('/leave-settings', [LeaveSettingController::class, 'update']);
        Route::put('/approval-settings', [ApprovalSettingController::class, 'update']);
        Route::put('/system-settings', [SystemSettingController::class, 'update']);
    });


    /*
    |--------------------------------------------------------------------------
    | LEAVE TYPES
    |--------------------------------------------------------------------------
    */

    // Both Employee and Admin can view leave types
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);

    // Only Admin can manage leave types
    Route::middleware('role:admin')->group(function () {
        Route::post('/leave-types', [LeaveTypeController::class, 'store']);
        Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy']);
    });
});
