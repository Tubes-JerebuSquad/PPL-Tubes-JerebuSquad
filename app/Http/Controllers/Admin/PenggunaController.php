<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPenggunaRequest;
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
}