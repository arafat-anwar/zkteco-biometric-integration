<?php

use Illuminate\Support\Facades\Route;
use Modules\Credentials\Http\Controllers\OrganizationController;
use Modules\Credentials\Http\Controllers\DeviceController;
use Modules\Credentials\Http\Controllers\EmployeeController;
use Modules\Credentials\Http\Controllers\BranchController;

Route::middleware('auth')->prefix('credentials')->group(function () {
    Route::get('organizations/data', [OrganizationController::class, 'data'])->name('organizations.data');
    Route::resource('organizations', OrganizationController::class)->names('organizations');
    Route::get('devices/data', [DeviceController::class, 'data'])->name('devices.data');
    Route::resource('devices', DeviceController::class)->names('devices');
    Route::get('employees/data', [EmployeeController::class, 'data'])->name('employees.data');
    Route::resource('employees', EmployeeController::class)->names('employees');
    Route::get('branches/data', [BranchController::class, 'data'])->name('branches.data');
    Route::resource('branches', BranchController::class)->names('branches');
});
