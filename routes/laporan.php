<?php

use App\Http\Controllers\Masyarakat\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'masyarakat'])
    ->prefix('masyarakat/laporan')
    ->name('masyarakat.laporan.')
    ->group(function (): void {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/{laporan}', [LaporanController::class, 'show'])->name('show');
        Route::get('/{laporan}/perbaiki', [LaporanController::class, 'edit'])->name('edit');
        Route::put('/{laporan}/perbaiki', [LaporanController::class, 'update'])->name('update');
        Route::get('/{laporan}/media/{berkasMedia}', [LaporanController::class, 'media'])->name('media');
    });
