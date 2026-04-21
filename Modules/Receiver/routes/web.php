<?php

use Illuminate\Support\Facades\Route;
use Modules\Receiver\Http\Controllers\ReceiverController;
use Modules\Receiver\Http\Controllers\AttendanceEntryController;

// Internal receive endpoint (token-protected, no auth session needed)
Route::post('/receiver/receive', [ReceiverController::class, 'receive'])->name('receiver.receive');

// Authenticated web routes
Route::middleware('auth')->prefix('receiver')->group(function () {
    Route::get('/', [ReceiverController::class, 'index'])->name('receiver.index');
    Route::get('/logs', [ReceiverController::class, 'logs'])->name('receiver.logs');
});

// Attendance entries CRUD
Route::middleware('auth')->group(function () {
    Route::get('entries/data', [AttendanceEntryController::class, 'data'])->name('entries.data');
    Route::resource('entries', AttendanceEntryController::class)->names('entries');
});
