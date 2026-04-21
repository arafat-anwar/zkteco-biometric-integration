<?php

use Illuminate\Support\Facades\Route;
use Modules\Pusher\Http\Controllers\PusherController;
use Modules\Pusher\Http\Controllers\MdbImportController;

Route::middleware('auth')->prefix('pusher')->group(function () {
    Route::get('/', [PusherController::class, 'index'])->name('pusher.index');
    Route::get('/device/{device}', [PusherController::class, 'showDevice'])->name('pusher.device');
    Route::post('/device/{device}/preview', [PusherController::class, 'preview'])->name('pusher.preview');
    Route::post('/device/{device}/push', [PusherController::class, 'push'])->name('pusher.push');
});

// MDB file import endpoints (file upload → sync to DB)
Route::middleware('auth')->prefix('mdb-import')->group(function () {
    Route::post('/devices', [MdbImportController::class, 'importDevices'])->name('mdb.import.devices');
    Route::post('/employees', [MdbImportController::class, 'importEmployees'])->name('mdb.import.employees');
    Route::post('/branches', [MdbImportController::class, 'importBranches'])->name('mdb.import.branches');
    Route::post('/entries', [MdbImportController::class, 'importEntries'])->name('mdb.import.entries');
});
