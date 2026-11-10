@extends('layouts.app')

@section('title', 'Dashboard Petugas Lapangan - RimbaWatch')
@section('breadcrumb', 'Dashboard Petugas')

@section('content')
<div class="space-y-6">
    <!-- Banner Sambutan Petugas Lapangan -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-3 py-1 text-xs font-semibold text-rimba-700 mb-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-600"></span>
                    Portal Petugas Lapangan RimbaWatch
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-ink-900">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}!
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    Anda masuk dengan peran <strong class="text-rimba-700">Petugas Lapangan</strong>. Tinjau surat tugas inspeksi, lakukan peninjauan fisik di koordinat kawasan, dan kirim formulir hasil inspeksi.
                </p>
            </div>

            <!-- Tombol Logout Cepat -->
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button 
                    type="submit" 
                    class="btn-danger inline-flex items-center gap-2 text-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar (Logout)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Ringkasan Statistik Tugas Petugas -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Tugas Saya -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Total Surat Tugas</span>
            <p class="mt-2 text-2xl font-bold text-ink-900">
                {{ $tugasSaya ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Inspeksi yang pernah ditugaskan</span>
        </div>

        <!-- Tugas Sedang Berjalan -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Tugas Aktif</span>
            <p class="mt-2 text-2xl font-bold text-amber-600 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                {{ $tugasBerjalan ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Menunggu peninjauan lapangan</span>
        </div>

        <!-- Inspeksi Selesai -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Inspeksi Selesai</span>
            <p class="mt-2 text-2xl font-bold text-emerald-700 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                {{ $inspeksiSelesai ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Laporan hasil telah terunggah</span>
        </div>

        <!-- Kawasan Konservasi -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Kawasan Terdaftar</span>
            <p class="mt-2 text-2xl font-bold text-ink-900">
                {{ $totalKawasan ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Wilayah pantauan terintegrasi</span>
        </div>
    </div>

    <!-- Modul Lapangan Petugas -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Akses Cepat Petugas -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rimba-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Menu Kerja Lapangan
            </h2>
            <div class="space-y-3">
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Daftar Surat Tugas</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Lihat rincian lokasi sasaran, batas waktu, dan instruksi inspeksi</span>
                </a>
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Input Hasil Inspeksi</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Unggah foto bukti lapangan, catatan vegetasi, dan kondisi fisik</span>
                </a>
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Peta Penunjuk Arah</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Buka koordinat GPS kawasan tujuan peninjauan</span>
                </a>
            </div>
        </div>

        <!-- Tabel Tugas Inspeksi Terbaru -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center justify-between">
                <span>Daftar Tugas Inspeksi Anda</span>
                <span class="text-xs font-semibold text-ink-400">Total: {{ count($daftarTugas) }} tugas</span>
            </h2>

            @if (count($daftarTugas) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-line bg-rimba-50/50 text-xs uppercase text-ink-400">
                            <tr>
                                <th class="py-2.5 px-3">No. Tugas</th>
                                <th class="py-2.5 px-3">Instruksi</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Tenggat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($daftarTugas as $tugas)
                                <tr class="hover:bg-rimba-50/40">
                                    <td class="py-3 px-3 font-mono text-xs text-ink-500">#{{ $tugas->tugas_inspeksi_id ?? $tugas->id }}</td>
                                    <td class="py-3 px-3 font-medium text-ink-900">{{ $tugas->instruksi ?? 'Inspeksi Lapangan' }}</td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $tugas->status_tugas ?? 'ASSIGNED' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-xs text-ink-400">{{ $tugas->tenggat_waktu ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-line p-8 text-center text-sm text-ink-400">
                    <svg class="mx-auto h-8 w-8 text-ink-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Belum ada surat tugas aktif yang ditugaskan kepada Anda saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
