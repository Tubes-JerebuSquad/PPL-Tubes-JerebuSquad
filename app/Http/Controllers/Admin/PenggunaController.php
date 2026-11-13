<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPenggunaRequest;
use App\Http\Requests\Admin\UbahPenggunaRequest;
use App\Models\LogAudit;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    public function create(): View
    {
        return view('admin.pengguna.create', [
            'daftarPeran' => Peran::orderBy('nama_peran')->get(),
        ]);
    }

    public function store(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password_hash'] = $data['password'];
        unset($data['password']);
        $data['status_akun'] = Pengguna::STATUS_AKTIF;

        DB::transaction(function () use ($request, $data) {
            $pengguna = Pengguna::create($data);

            LogAudit::catat($request->user(), $pengguna, LogAudit::AKSI_AKUN_DIBUAT, [
                'nama_lengkap' => $pengguna->nama_lengkap,
                'email' => $pengguna->email,
                'peran_id' => $pengguna->peran_id,
            ]);
        });

        return redirect()
            ->route('admin.pengguna.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Pengguna $pengguna): View
    {
        return view('admin.pengguna.edit', [
            'pengguna' => $pengguna,
            'daftarPeran' => Peran::orderBy('nama_peran')->get(),
        ]);
    }

    public function update(UbahPenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        $data = $request->validated();
        $password = $data['password'] ?? null;
        unset($data['password']);

        if (filled($password)) {
            $data['password_hash'] = $password;
        }

        DB::transaction(function () use ($request, $pengguna, $data) {
            $pengguna->fill($data);
            $rincian = $this->rincianPerubahan($pengguna);
            $pengguna->save();

            $perubahanPeran = Arr::only($rincian, ['peran_id']);
            $perubahanLain = Arr::except($rincian, ['peran_id']);

            if ($perubahanPeran !== []) {
                LogAudit::catat($request->user(), $pengguna, LogAudit::AKSI_PERAN_DIUBAH, $perubahanPeran);
            }

            if ($perubahanLain !== []) {
                LogAudit::catat($request->user(), $pengguna, LogAudit::AKSI_DATA_DIUBAH, $perubahanLain);
            }
        });

        return redirect()
            ->route('admin.pengguna.index')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function nonaktifkan(Request $request, Pengguna $pengguna): RedirectResponse
    {
        if ($request->user()->is($pengguna)) {
            return back()->withErrors(['akun' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        DB::transaction(function () use ($request, $pengguna) {
            $pengguna->forceFill([
                'status_akun' => Pengguna::STATUS_NONAKTIF,
                'dinonaktifkan_pada' => now(),
            ])->save();

            LogAudit::catat($request->user(), $pengguna, LogAudit::AKSI_AKUN_DINONAKTIFKAN);
        });

        return back()->with('status', 'Akun berhasil dinonaktifkan.');
    }

    public function aktifkan(Request $request, Pengguna $pengguna): RedirectResponse
    {
        DB::transaction(function () use ($request, $pengguna) {
            $pengguna->forceFill([
                'status_akun' => Pengguna::STATUS_AKTIF,
                'dinonaktifkan_pada' => null,
            ])->save();

            LogAudit::catat($request->user(), $pengguna, LogAudit::AKSI_AKUN_DIAKTIFKAN);
        });

        return back()->with('status', 'Akun berhasil diaktifkan.');
    }

    private function rincianPerubahan(Pengguna $pengguna): array
    {
        $rincian = [];

        foreach ($pengguna->getDirty() as $kolom => $baru) {
            if ($kolom === 'password_hash') {
                $rincian['kata_sandi'] = 'diganti';
                continue;
            }

            $rincian[$kolom] = ['dari' => $pengguna->getOriginal($kolom), 'menjadi' => $baru];
        }

        return $rincian;
    }
}