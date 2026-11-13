<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAuditController extends Controller
{
    private const LABEL_AKSI = [
        LogAudit::AKSI_AKUN_DIBUAT => 'Akun dibuat',
        LogAudit::AKSI_DATA_DIUBAH => 'Data diubah',
        LogAudit::AKSI_PERAN_DIUBAH => 'Peran diubah',
        LogAudit::AKSI_AKUN_DINONAKTIFKAN => 'Akun dinonaktifkan',
        LogAudit::AKSI_AKUN_DIAKTIFKAN => 'Akun diaktifkan',
    ];

    public function index(Request $request): View
    {
        $aksi = $request->query('aksi');

        if (! array_key_exists((string) $aksi, self::LABEL_AKSI)) {
            $aksi = null;
        }

        $daftar = LogAudit::with(['pelaku', 'sasaran'])
            ->when($aksi, fn ($query) => $query->where('aksi', $aksi))
            ->orderByDesc('dibuat_pada')
            ->orderByDesc('log_audit_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit.index', [
            'daftar' => $daftar,
            'labelAksi' => self::LABEL_AKSI,
            'aksiDipilih' => $aksi,
        ]);
    }
}