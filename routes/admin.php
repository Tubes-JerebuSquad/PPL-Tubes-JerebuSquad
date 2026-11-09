<?php

use App\Http\Controllers\Admin\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('pengguna', PenggunaController::class)->only(['index', 'store', 'update']);

    Route::patch('pengguna/{pengguna}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])
        ->name('pengguna.nonaktifkan');

    Route::patch('pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])
        ->name('pengguna.aktifkan');
});