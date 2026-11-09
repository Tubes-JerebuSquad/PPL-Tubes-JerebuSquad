<?php

use App\Http\Controllers\Admin\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('pengguna', PenggunaController::class)->only(['index', 'store']);
});