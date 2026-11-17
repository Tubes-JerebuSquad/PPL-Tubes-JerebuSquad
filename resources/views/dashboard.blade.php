@extends('layouts.app')

@section('title', 'Portal Pengguna - RimbaWatch')
@section('breadcrumb', 'Portal Pengguna')

@section('content')
<div class="space-y-6">
    <!-- Banner Sambutan Pengguna -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-3 py-1 text-xs font-semibold text-rimba-700 mb-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-600"></span>
                    Portal {{ auth()->user()->peran?->nama_peran ?? 'Pengguna' }} RimbaWatch
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-ink-900">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}!
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    Anda berhasil masuk ke sistem dengan peran <strong class="text-rimba-700">{{ auth()->user()->peran?->nama_peran ?? 'Pengguna' }}</strong>.
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

    <!-- Informasi Profil & Hak Akses Sesuai Peran -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kartu Identitas Akun -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rimba-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profil Akun
            </h2>

            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-ink-400 font-medium">Nama Lengkap</dt>
                    <dd class="font-semibold text-ink-900 mt-0.5">{{ auth()->user()->nama_lengkap }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-400 font-medium">Email Terdaftar</dt>
                    <dd class="font-medium text-ink-700 mt-0.5">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-400 font-medium">Nomor WhatsApp / Telepon</dt>
                    <dd class="font-medium text-ink-700 mt-0.5">{{ auth()->user()->telepon ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-400 font-medium">Peran Saat Ini</dt>
                    <dd class="mt-0.5">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-2.5 py-0.5 text-xs font-semibold text-rimba-700">
                            {{ auth()->user()->peran?->nama_peran ?? 'Pengguna' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-400 font-medium">Status Akun</dt>
                    <dd class="mt-0.5">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Aktif (ACTIVE)
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Kartu Deskripsi Peran & Tanggung Jawab -->
        <div class="rounded-2xl border border-line bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-base font-bold text-ink-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-rimba-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Wewenang & Cakupan Akses
            </h2>

            <div class="rounded-xl bg-rimba-50 p-4 border border-line mb-4">
                <p class="text-sm font-semibold text-rimba-900">
                    {{ auth()->user()->peran?->deskripsi ?? 'Akses pengguna dalam sistem RimbaWatch.' }}
                </p>
            </div>

            @php
                $namaPeran = auth()->user()->peran?->nama_peran;
            @endphp

            @if ($namaPeran === 'Pengawas')
                <div class="space-y-2 text-sm text-ink-700">
                    <p class="font-semibold text-ink-900">Tugas & Fungsi Utama Pengawas:</p>
                    <ul class="list-disc list-inside space-y-1 text-ink-600">
                        <li>Memverifikasi dan memvalidasi laporan aduan masyarakat yang masuk.</li>
                        <li>Menugaskan petugas lapangan untuk melakukan inspeksi ke titik koordinat laporan.</li>
                        <li>Memonitor progres penanganan insiden dan kawasan konservasi.</li>
                    </ul>
                </div>
            @elseif ($namaPeran === 'Petugas Lapangan')
                <div class="space-y-2 text-sm text-ink-700">
                    <p class="font-semibold text-ink-900">Tugas & Fungsi Utama Petugas Lapangan:</p>
                    <ul class="list-disc list-inside space-y-1 text-ink-600">
                        <li>Menerima surat tugas inspeksi kawasan hutan dari Pengawas.</li>
                        <li>Melakukan peninjauan langsung ke lapangan dengan koordinat GIS / Titik Panas.</li>
                        <li>Mengunggah bukti foto dan mengisi formulir hasil inspeksi.</li>
                    </ul>
                </div>
            @elseif ($namaPeran === 'Masyarakat')
                <div class="space-y-2 text-sm text-ink-700">
                    <p class="font-semibold text-ink-900">Fasilitas Partisipasi Warga:</p>
                    <ul class="list-disc list-inside space-y-1 text-ink-600">
                        <li>Mengirimkan laporan aduan perusakan atau kebakaran hutan.</li>
                        <li>Memantau status tindak lanjut laporan yang telah diajukan.</li>
                        <li>Mengikuti program kegiatan konservasi dan penanaman pohon.</li>
                    </ul>
                </div>
            @else
                <p class="text-sm text-ink-600">
                    Anda berhasil masuk dengan akun pengguna RimbaWatch. Fitur modul lanjutan sedang disiapkan untuk peran ini.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
