<?php

use Illuminate\Support\Facades\Route;
use Modules\Docs\Http\Controllers\DocsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('docs', DocsController::class)->names('docs');
});
