<?php

use App\Http\Controllers\Admin\BatasKawasanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin/kawasan/{kawasan}')
    ->name('admin.kawasan.batas.')
    ->group(function (): void {
        Route::get('/batas', [BatasKawasanController::class, 'index'])->name('index');
        Route::post('/batas/digitasi', [BatasKawasanController::class, 'simpanDigitasi'])->name('digitasi');
        Route::post('/batas/impor', [BatasKawasanController::class, 'imporGeojson'])->name('impor');
    });