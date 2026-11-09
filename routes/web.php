<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Masyarakat\DashboardController as MasyarakatDashboardController;
use App\Http\Controllers\Pengawas\DashboardController as PengawasDashboardController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use Illuminate\Support\Facades\Route;

// Halaman awal & login (mendukung request GET & POST pada '/' dan '/login')
Route::get('/', [LoginController::class, 'create']);
Route::post('/', [LoginController::class, 'store']);

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

// Rute yang membutuhkan autentikasi
Route::middleware('auth')->group(function () {
    // Portal umum yang secara otomatis mengarahkan ke dashboard per peran
    Route::get('/dashboard', [DashboardController::class, 'portal'])->name('dashboard');
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    // Dashboard Peran Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });

    // Dashboard Peran Pengawas
    Route::prefix('pengawas')->name('pengawas.')->group(function () {
        Route::get('/dashboard', [PengawasDashboardController::class, 'index'])->name('dashboard');
    });

    // Dashboard Peran Petugas Lapangan
    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
    });

    // Dashboard Peran Masyarakat / Warga
    Route::prefix('masyarakat')->name('masyarakat.')->group(function () {
        Route::get('/dashboard', [MasyarakatDashboardController::class, 'index'])->name('dashboard');
    });
});
