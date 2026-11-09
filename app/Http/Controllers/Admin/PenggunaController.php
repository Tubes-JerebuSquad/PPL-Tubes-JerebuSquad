<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPenggunaRequest;
use App\Http\Requests\Admin\UbahPenggunaRequest;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Pengguna::with('peran')
            ->cari($request->query('cari'))
            ->berperan($request->integer('peran_id') ?: null)
            ->berstatus($request->query('status'))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', [
            'daftar' => $daftar,
            'daftarPeran' => Peran::orderBy('nama_peran')->get(),
        ]);
    }

    public function store(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password_hash'] = $data['password'];
        unset($data['password']);
        $data['status_akun'] = Pengguna::STATUS_AKTIF;

        Pengguna::create($data);

        return redirect()
            ->route('admin.pengguna.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UbahPenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        $data = $request->validated();
        $password = $data['password'] ?? null;
        unset($data['password']);

        if (filled($password)) {
            $data['password_hash'] = $password;
        }

        $pengguna->update($data);

        return redirect()
            ->route('admin.pengguna.index')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function nonaktifkan(Request $request, Pengguna $pengguna): RedirectResponse
    {
        if ($request->user()->is($pengguna)) {
            return back()->withErrors(['akun' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $pengguna->forceFill([
            'status_akun' => Pengguna::STATUS_NONAKTIF,
            'dinonaktifkan_pada' => now(),
        ])->save();

        return back()->with('status', 'Akun berhasil dinonaktifkan.');
    }

    public function aktifkan(Pengguna $pengguna): RedirectResponse
    {
        $pengguna->forceFill([
            'status_akun' => Pengguna::STATUS_AKTIF,
            'dinonaktifkan_pada' => null,
        ])->save();

        return back()->with('status', 'Akun berhasil diaktifkan.');
    }
}