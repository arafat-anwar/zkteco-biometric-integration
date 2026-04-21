<?php

use Illuminate\Support\Facades\Route;
use Modules\API\Http\Controllers\AuthApiController;
use Modules\API\Http\Controllers\AttendanceApiController;
use Modules\API\Http\Controllers\BranchApiController;
use Modules\API\Http\Controllers\EmployeeApiController;

// Public auth routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.login');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.logout');
        Route::get('/auth/me', [AuthApiController::class, 'me'])->name('api.me');

        // Attendance
        Route::get('/attendance', [AttendanceApiController::class, 'index'])->name('api.attendance.index');
        Route::post('/attendance/receive', [AttendanceApiController::class, 'receive'])->name('api.attendance.receive');
        Route::get('/attendance/push-logs', [AttendanceApiController::class, 'pushLogs'])->name('api.attendance.push-logs');

        // Employees (managers+)
        Route::apiResource('employees', EmployeeApiController::class)->names([
            'index'   => 'api.employees.index',
            'store'   => 'api.employees.store',
            'show'    => 'api.employees.show',
            'update'  => 'api.employees.update',
            'destroy' => 'api.employees.destroy',
        ]);

        // Branches (managers+)
        Route::apiResource('branches', BranchApiController::class)->names([
            'index'   => 'api.branches.index',
            'store'   => 'api.branches.store',
            'show'    => 'api.branches.show',
            'update'  => 'api.branches.update',
            'destroy' => 'api.branches.destroy',
        ]);
    });
});
