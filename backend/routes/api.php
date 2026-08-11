<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:employee')->group(function () {
        Route::get('/my-profile', [EmployeeController::class, 'myProfile']);
        // Settings/Profile
        Route::get('/profile', [EmployeeController::class, 'myProfile']);
        Route::put('/profile', [EmployeeController::class, 'updateProfile']);

        Route::put('/profile/email', [EmployeeController::class, 'updateEmail']);

        Route::put('/profile/password', [EmployeeController::class, 'updatePassword']);

        Route::put('/profile/phone', [EmployeeController::class, 'updatePhone']);

        // Submit leave
        Route::post('/leave-applications', [LeaveController::class, 'store']);

        // View own leaves
        Route::get('/my-leave-applications', [LeaveController::class, 'myLeaves']);

        // View single own leave
        Route::get('/my-leave-applications/{id}', [LeaveController::class, 'myLeave']);
        // View own balance
        Route::get('/my-leave-balance', [LeaveBalanceController::class, 'myBalance']);
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance']);
        Route::get('/my-attendance/{id}', [AttendanceController::class, 'myAttendanceRecord']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        Route::get('admin/dashboard', [DashboardController::class, 'index']);
        // Employee management
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::put('/employees/{id}', [EmployeeController::class, 'update']);

        // Leave management
        Route::get('/leave-applications', [LeaveController::class, 'index']);
        Route::get('/leave-applications/{id}', [LeaveController::class, 'show']);

        // Approve / Reject / Update status
        Route::put('/leave-applications/{id}', [LeaveController::class, 'updateStatus']);

        Route::post('/leave-credits', [LeaveCreditController::class, 'store']);
        Route::get('/leave-credits', [LeaveCreditController::class, 'index']);
        Route::get('/leave-credits/{employee_id}', [LeaveCreditController::class, 'show']);
        Route::put('/leave-credits/{id}', [LeaveCreditController::class, 'update']);
        Route::get('/leave-balances', [LeaveBalanceController::class, 'index']);
        Route::get('/leave-balances/{employee_id}', [LeaveBalanceController::class, 'show']);
        Route::put('/leave-balances/{employee_id}', [LeaveBalanceController::class, 'update']);
        Route::post(
            '/leave-credits/{id}/apply',
            [LeaveCreditController::class, 'apply']
        );
        Route::post('/attendance', [AttendanceController::class, 'store']);

        Route::get('/attendance', [AttendanceController::class, 'index']);

        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);

        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/leave-summary', [ReportController::class, 'leaveSummary']);
        Route::get('/reports/leave-credits', [ReportController::class, 'leaveCredits']);
        Route::post('/reports/attendance', [ReportController::class, 'generateAttendanceReport']);
        Route::get('/reports/download/{id}', [ReportController::class, 'download']);
        Route::get('/reports/employees', [ReportController::class, 'employeeReport']);
        // ADMIN SETTINGS

        Route::get(
            '/admin/profile',
            [AdminController::class, 'profile']
        );

        Route::put(
            '/admin/profile',
            [AdminController::class, 'updateProfile']
        );
        Route::put(
            '/admin/profile',
            [AdminController::class, 'updateProfile']
        );

        Route::put(
            '/admin/email',
            [AdminController::class, 'updateEmail']
        );


        Route::put(
            '/admin/password',
            [AdminController::class, 'updatePassword']
        );
    });



    /*
    |--------------------------------------------------------------------------
    | LEAVE TYPES (BOTH ROLES READ)
    |--------------------------------------------------------------------------
    */
    Route::get('/leave-types', [LeaveTypeController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/leave-types', [LeaveTypeController::class, 'store']);
        Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy']);
    });
});
