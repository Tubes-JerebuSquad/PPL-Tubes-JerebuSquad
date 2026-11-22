<?php

use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\Pengawas\MateriEdukasiController;
use Illuminate\Support\Facades\Route;

// Halaman baca materi edukasi (publik & Masyarakat; hanya materi yang diterbitkan)
Route::prefix('edukasi')->name('edukasi.')->group(function (): void {
    Route::get('/', [EdukasiController::class, 'index'])->name('index');
    Route::get('/{materiEdukasi}', [EdukasiController::class, 'show'])
        ->whereNumber('materiEdukasi')
        ->name('show');
});

// Pengelolaan materi edukasi khusus Pengawas aktif
Route::middleware(['auth', 'pengawas'])
    ->prefix('pengawas')
    ->name('pengawas.')
    ->group(function (): void {
        Route::resource('edukasi', MateriEdukasiController::class)
            ->parameters(['edukasi' => 'materiEdukasi'])
            ->except(['destroy']);

        Route::patch('edukasi/{materiEdukasi}/terbitkan', [MateriEdukasiController::class, 'terbitkan'])
            ->whereNumber('materiEdukasi')
            ->name('edukasi.terbitkan');

        Route::patch('edukasi/{materiEdukasi}/arsipkan', [MateriEdukasiController::class, 'arsipkan'])
            ->whereNumber('materiEdukasi')
            ->name('edukasi.arsipkan');
    });
