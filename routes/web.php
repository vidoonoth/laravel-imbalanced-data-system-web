<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\DetectionController;

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
    Route::get('/dashboard/ip-location', [DetectionController::class, 'ipLocation'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.ip-location');

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

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
