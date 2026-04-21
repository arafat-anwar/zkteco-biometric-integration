<?php

use Illuminate\Support\Facades\Route;
use Modules\Pusher\Http\Controllers\PusherController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pushers', PusherController::class)->names('pusher');
});
