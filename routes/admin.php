<?php

use App\Http\Controllers\Admin\LogAuditController;
use App\Http\Controllers\Admin\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('pengguna', PenggunaController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::patch('pengguna/{pengguna}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])
        ->name('pengguna.nonaktifkan');

    Route::patch('pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])
               ->name('pengguna.aktifkan');

    Route::get('audit', [LogAuditController::class, 'index'])->name('audit.index');
     });
