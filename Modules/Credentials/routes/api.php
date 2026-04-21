<?php

use Illuminate\Support\Facades\Route;
use Modules\Credentials\Http\Controllers\CredentialsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('credentials', CredentialsController::class)->names('credentials');
});
