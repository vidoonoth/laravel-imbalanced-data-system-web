<?php

use App\Http\Controllers\Api\DetectionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.apikey')->group(function () {
    Route::get('/dashboard', [DetectionApiController::class, 'dashboard']);
    Route::get('/dashboard/suspicious-ips', [DetectionApiController::class, 'suspiciousIps']);
    Route::get('/dashboard/suspicious-ips/detail', [DetectionApiController::class, 'suspiciousIpDetail']);
    Route::get('/dashboard/suspicious-ips/location', [DetectionApiController::class, 'suspiciousIpLocation']);
});
