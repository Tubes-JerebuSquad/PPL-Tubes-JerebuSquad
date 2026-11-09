@extends('layouts.app')

@section('title', 'Dashboard Pengawas - RimbaWatch')
@section('breadcrumb', 'Dashboard Pengawas')

@section('content')
<div class="space-y-6">
    <!-- Banner Sambutan Pengawas -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-3 py-1 text-xs font-semibold text-rimba-700 mb-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-600"></span>
                    Portal Pengawas RimbaWatch
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-ink-900">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}!
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    Anda masuk dengan peran <strong class="text-rimba-700">Pengawas</strong>. Verifikasi laporan masyarakat, tetapkan surat tugas untuk petugas lapangan, dan pantau status kelestarian kawasan.
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

    <!-- Ringkasan Statistik Pengawas -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Laporan Masuk -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Total Laporan Masuk</span>
            <p class="mt-2 text-2xl font-bold text-ink-900">
                {{ $totalLaporan ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Semua laporan dari masyarakat</span>
        </div>

        <!-- Perlu Verifikasi -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Perlu Verifikasi</span>
            <p class="mt-2 text-2xl font-bold text-amber-600 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                {{ $laporanPerluVerifikasi ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Menunggu validasi Pengawas</span>
        </div>

        <!-- Tugas Inspeksi -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Tugas Inspeksi</span>
            <p class="mt-2 text-2xl font-bold text-rimba-700">
                {{ $tugasInspeksi ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Surat tugas ke petugas lapangan</span>
        </div>

        <!-- Titik Panas Terpantau -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-danger-ink uppercase tracking-wider">Titik Panas (Hotspot)</span>
            <p class="mt-2 text-2xl font-bold text-danger-ink">
                {{ $titikPanas ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Deteksi satelit sensor termal</span>
        </div>
    </div>

    <!-- Modul Cepat Pengawas -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Modul Tindakan Cepat -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rimba-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Tindakan Cepat Pengawas
            </h2>
            <div class="space-y-3">
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Verifikasi Aduan Warga</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Periksa bukti foto dan kesesuaian koordinat laporan</span>
                </a>
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Tugaskan Petugas Lapangan</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Terbitkan surat penugasan inspeksi ke titik insiden</span>
                </a>
                <a href="#" class="block p-3 rounded-xl border border-line hover:border-rimba-500 hover:bg-rimba-50/50 transition">
                    <span class="font-semibold text-sm text-ink-900 block">Peta Kawasan & Titik Panas</span>
                    <span class="text-xs text-ink-500 block mt-0.5">Analisis spasial persebaran hotspot dan batas kawasan</span>
                </a>
            </div>
        </div>

        <!-- Tabel Laporan Terbaru yang Masuk -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center justify-between">
                <span>Daftar Laporan Terkini</span>
                <span class="text-xs font-semibold text-ink-400">Total: {{ count($laporanTerbaru) }} antrean</span>
            </h2>

            @if (count($laporanTerbaru) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-line bg-rimba-50/50 text-xs uppercase text-ink-400">
                            <tr>
                                <th class="py-2.5 px-3">ID</th>
                                <th class="py-2.5 px-3">Judul Laporan</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($laporanTerbaru as $lap)
                                <tr class="hover:bg-rimba-50/40">
                                    <td class="py-3 px-3 font-mono text-xs text-ink-500">#{{ $lap->nomor_laporan ?? $lap->id }}</td>
                                    <td class="py-3 px-3 font-medium text-ink-900">{{ $lap->kategori ?? 'Laporan Lingkungan' }}</td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ strtoupper($lap->status ?? 'MENUNGGU') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-xs text-ink-400">{{ $lap->created_at ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-line p-8 text-center text-sm text-ink-400">
                    <svg class="mx-auto h-8 w-8 text-ink-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Belum ada antrean laporan aduan yang perlu diverifikasi saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
