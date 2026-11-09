@extends('layouts.app')

@section('title', 'Dashboard Warga - RimbaWatch')
@section('breadcrumb', 'Dashboard Warga')

@section('content')
<div class="space-y-6">
    <!-- Banner Sambutan Warga / Masyarakat -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-3 py-1 text-xs font-semibold text-rimba-700 mb-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-600"></span>
                    Portal Komunitas dan Warga RimbaWatch
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-ink-900">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}!
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    Anda masuk dengan peran <strong class="text-rimba-700">Masyarakat</strong>. Laporkan indikasi kebakaran atau perusakan hutan di sekitarmu, dan dukung kelestarian hutan Indonesia.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Tombol Buat Laporan Cepat -->
                <a 
                    href="{{ url('/lapor') }}" 
                    class="btn-primary inline-flex items-center gap-2 text-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Buat Laporan Baru</span>
                </a>

                <!-- Tombol Logout Cepat -->
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button 
                        type="submit" 
                        class="btn-danger inline-flex items-center gap-2 text-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Partisipasi Warga -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Laporan Saya -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Laporan Diajukan</span>
            <p class="mt-2 text-2xl font-bold text-ink-900">
                {{ $laporanSaya ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Aduan yang pernah Anda kirim</span>
        </div>

        <!-- Laporan Sedang Diproses -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Sedang Ditangani</span>
            <p class="mt-2 text-2xl font-bold text-amber-600 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                {{ $laporanDiproses ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Dalam tahap verifikasi / inspeksi</span>
        </div>

        <!-- Laporan Selesai -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Selesai Ditindaklanjuti</span>
            <p class="mt-2 text-2xl font-bold text-emerald-700 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                {{ $laporanSelesai ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Aduan tertangani oleh tim patroli</span>
        </div>

        <!-- Kegiatan Konservasi -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-rimba-700 uppercase tracking-wider">Aksi Konservasi</span>
            <p class="mt-2 text-2xl font-bold text-rimba-700">
                {{ $kegiatanKonservasi ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Agenda penanaman & edukasi</span>
        </div>
    </div>

    <!-- Riwayat Aduan Saya & Program Konservasi -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kartu Edukasi Partisipasi -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rimba-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Partisipasi Lingkungan
            </h2>
            <div class="space-y-3">
                <div class="rounded-xl bg-rimba-50 p-4 border border-line">
                    <h3 class="text-sm font-bold text-rimba-900">Tips Pengambilan Bukti Foto</h3>
                    <p class="text-xs text-ink-600 mt-1 leading-relaxed">
                        Pastikan GPS perangkat Anda aktif saat mengambil foto kawasan hutan agar titik koordinat otomatis tercatat akurat.
                    </p>
                </div>
                <div class="rounded-xl border border-line p-4">
                    <h3 class="text-sm font-bold text-ink-900">Layanan Darurat Karhutla</h3>
                    <p class="text-xs text-ink-500 mt-1">
                        Jika melihat kepulan asap besar atau kebakaran aktif, prioritaskan keselamatan diri dan segera hubungi posko terdekat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Aduan Saya -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center justify-between">
                <span>Riwayat Laporan Aduan Saya</span>
                <span class="text-xs font-semibold text-ink-400">Total: {{ count($riwayatLaporan) }} riwayat</span>
            </h2>

            @if (count($riwayatLaporan) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-line bg-rimba-50/50 text-xs uppercase text-ink-400">
                            <tr>
                                <th class="py-2.5 px-3">Kode</th>
                                <th class="py-2.5 px-3">Judul Aduan</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Tanggal Lapor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($riwayatLaporan as $item)
                                <tr class="hover:bg-rimba-50/40">
                                    <td class="py-3 px-3 font-mono text-xs text-ink-500">#{{ $item->nomor_laporan ?? $item->id }}</td>
                                    <td class="py-3 px-3 font-medium text-ink-900">{{ $item->kategori ?? 'Laporan Lingkungan' }}</td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rimba-100 text-rimba-700">
                                            {{ strtoupper($item->status ?? 'TERKIRIM') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-xs text-ink-400">{{ $item->created_at ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-line p-8 text-center text-sm text-ink-400">
                    <svg class="mx-auto h-8 w-8 text-ink-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Anda belum pernah mengirim laporan aduan. Mulai dengan menekan tombol <strong>+ Buat Laporan Baru</strong> di atas jika menemukan insiden hutan.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
