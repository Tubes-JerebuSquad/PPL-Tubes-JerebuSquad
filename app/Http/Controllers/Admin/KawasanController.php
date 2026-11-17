<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kawasan;
use Illuminate\View\View;

class KawasanController extends Controller
{
    public function index(): View
    {
        $daftar = Kawasan::with('wilayah')
            ->orderBy('nama_kawasan')
            ->paginate(15);

        return view('admin.kawasan.index', [
            'daftar' => $daftar,
        ]);
    }

    public function show(Kawasan $kawasan): View
    {
        $kawasan->load(['wilayah', 'pembuat']);

        return view('admin.kawasan.show', [
            'kawasan' => $kawasan,
        ]);
    }
}
