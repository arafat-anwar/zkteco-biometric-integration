<?php

use Illuminate\Support\Facades\Route;
use Modules\Receiver\Http\Controllers\ReceiverController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('receivers', ReceiverController::class)->names('receiver');
});
