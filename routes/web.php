<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\MLController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
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

    // Laporan
    Route::get('/laporan', [ReportController::class, 'index'])
        ->middleware('permission:report.view')
        ->name('report.index');
    Route::get('/laporan/export-pdf', [ReportController::class, 'exportPdf'])
        ->middleware('permission:report.view')
        ->name('report.export.pdf');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::middleware('permission:users.manage')->group(function () {
                Route::resource('users', AdminUserController::class)->except('show');
            });

            Route::middleware('permission:permissions.manage')->group(function () {
                Route::get('permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
                Route::get('permissions/{user}/edit', [AdminPermissionController::class, 'edit'])->name('permissions.edit');
                Route::put('permissions/{user}', [AdminPermissionController::class, 'update'])->name('permissions.update');
            });
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
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
