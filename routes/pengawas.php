<?php

use App\Http\Controllers\Pengawas\LaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'pengawas'])->prefix('pengawas')->name('pengawas.')->group(function (): void {
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::post('laporan/{laporan}/triase', [LaporanController::class, 'triase'])->name('laporan.triase');
});
