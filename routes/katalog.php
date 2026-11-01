<?php

use App\Http\Controllers\Katalog\SumberDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('katalog')->name('katalog.')->group(function (): void {
    Route::resource('sumber-data', SumberDataController::class)
        ->parameters(['sumber-data' => 'sumberData'])
        ->except(['destroy']);

    Route::patch('sumber-data/{sumberData}/aktifkan', [SumberDataController::class, 'aktifkan'])
        ->name('sumber-data.aktifkan');

    Route::patch('sumber-data/{sumberData}/nonaktifkan', [SumberDataController::class, 'nonaktifkan'])
        ->name('sumber-data.nonaktifkan');
});
