<?php

use App\Http\Controllers\Api\DetectionApiController;
use Illuminate\Support\Facades\Route;

Route::post('/detection/results', [DetectionApiController::class, 'store'])
    ->middleware('auth.apikey');
