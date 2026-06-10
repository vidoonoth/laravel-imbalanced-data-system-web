<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\MLController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('login');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Overview
    Route::get('/dashboard', [DetectionController::class, 'dashboard'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    Route::get('/dashboard/ip-activity', [DetectionController::class, 'ipActivity'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.ip-activity');

    // Detection/Inference
    Route::get('/detection', function () {
        return view('detection');
    })->middleware('permission:detection.run')->name('detection');

    Route::get('/riwayat-deteksi', [DetectionController::class, 'history'])
        ->middleware('permission:detection-history.view')
        ->name('detection.history');
    Route::get('/riwayat-deteksi/{scan}', [DetectionController::class, 'show'])
        ->middleware('permission:detection-history.view')
        ->name('detection.history.show');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('permission:users.manage')
        ->group(function () {
            Route::resource('users', AdminUserController::class)->except('show');
        });

    // ===== ML API Routes =====
    Route::prefix('api/ml')->name('ml.')->group(function () {
        // Health check
        Route::get('/health', [MLController::class, 'health'])->name('health');

        // Detection/Inference
        Route::post('/detect', [MLController::class, 'detect'])
            ->middleware('permission:detection.run')
            ->name('detect');
        Route::post('/predict-file', [MLController::class, 'predictFromFile'])
            ->middleware('permission:detection.run')
            ->name('predict.file');
        Route::post('/predict-batch', [MLController::class, 'predictBatch'])
            ->middleware('permission:detection.run')
            ->name('predict.batch');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
