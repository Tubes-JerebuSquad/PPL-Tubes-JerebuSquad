<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DaftarRequest;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DaftarController extends Controller
{
    public function create(): View
    {
        return view('auth.daftar');
    }

    public function store(DaftarRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $pengguna = Pengguna::create([
            'peran_id' => Peran::where('nama_peran', 'Masyarakat')->firstOrFail()->peran_id,
            'nama_lengkap' => $data['nama_lengkap'],
            'email' => $data['email'],
            'telepon' => $data['telepon'] ?? null,
            'password_hash' => $data['password'],
            'status_akun' => Pengguna::STATUS_AKTIF,
        ]);

        Auth::login($pengguna);
        $request->session()->regenerate();

        return redirect()
            ->route('masyarakat.dashboard')
            ->with('status', 'Pendaftaran berhasil. Selamat datang di RimbaWatch.');
    }
}