<?php

use App\Http\Controllers\Masyarakat\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'masyarakat'])
    ->prefix('masyarakat/laporan')
    ->name('masyarakat.laporan.')
    ->group(function (): void {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/{laporan}', [LaporanController::class, 'show'])->whereNumber('laporan')->name('show');
        Route::get('/{laporan}/perbaiki', [LaporanController::class, 'edit'])->whereNumber('laporan')->name('edit');
        Route::put('/{laporan}/perbaiki', [LaporanController::class, 'update'])->whereNumber('laporan')->name('update');
        Route::get('/{laporan}/media/{berkasMedia}', [LaporanController::class, 'media'])
            ->whereNumber(['laporan', 'berkasMedia'])
            ->name('media');
    });
