<?php

use App\Http\Controllers\Pengawas\KegiatanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'pengawas'])->prefix('pengawas')->name('pengawas.')->group(function (): void {
    Route::resource('kegiatan', KegiatanController::class)->except(['destroy']);

    Route::patch('kegiatan/{kegiatan}/batalkan', [KegiatanController::class, 'batalkan'])
        ->name('kegiatan.batalkan');
});