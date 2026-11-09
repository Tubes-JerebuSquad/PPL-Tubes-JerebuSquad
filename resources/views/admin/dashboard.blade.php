@extends('layouts.app')

@section('title', 'Dashboard Admin - RimbaWatch')
@section('breadcrumb', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    <!-- Banner Sambutan Admin -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rimba-100 px-3 py-1 text-xs font-semibold text-rimba-700 mb-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-600"></span>
                    Portal Administrasi RimbaWatch
                </span>
                <h1 class="text-2xl font-bold tracking-tight text-ink-900">
                    Selamat Datang, {{ auth()->user()->nama_lengkap }}!
                </h1>
                <p class="mt-1 text-sm text-ink-500">
                    Anda masuk dengan peran <strong class="text-rimba-700">{{ auth()->user()->peran?->nama_peran ?? 'Admin' }}</strong>. Kelola data master, pantauan kawasan, dan pengguna sistem melalui menu navigasi.
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

    <!-- Ringkasan Statistik Data Master -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Pengguna -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Total Pengguna</span>
            <p class="mt-2 text-2xl font-bold text-ink-900">
                {{ $totalPengguna ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Akun terdaftar dalam sistem</span>
        </div>

        <!-- Pengguna Aktif -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Pengguna Aktif</span>
            <p class="mt-2 text-2xl font-bold text-emerald-700 flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                {{ $penggunaAktif ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Berstatus aktif (ACTIVE)</span>
        </div>

        <!-- Total Peran -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Total Peran Akses</span>
            <p class="mt-2 text-2xl font-bold text-rimba-700">
                {{ $totalPeran ?? 0 }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">Peran dalam aplikasi</span>
        </div>

        <!-- Status Akun Anda -->
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">Status Akun Anda</span>
            <p class="mt-2 text-lg font-bold text-emerald-700 truncate">
                {{ auth()->user()->status_akun }}
            </p>
            <span class="text-xs text-ink-400 mt-1 block">{{ auth()->user()->email }}</span>
        </div>
    </div>

    <!-- Tabel Pengguna Terbaru -->
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-ink-900">Pengguna Terdaftar Terbaru</h2>
                <p class="text-xs text-ink-400">Daftar pengguna yang terakhir kali bergabung ke dalam platform.</p>
            </div>
            @if (\Illuminate\Support\Facades\Route::has('admin.pengguna.index'))
                <a href="{{ route('admin.pengguna.index') }}" class="text-xs font-semibold text-rimba-700 hover:underline">
                    Kelola Semua Pengguna &rarr;
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-line text-ink-400 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold">Nama Lengkap</th>
                        <th class="py-3 px-4 font-semibold">Email</th>
                        <th class="py-3 px-4 font-semibold">Peran</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Terdaftar Pada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($penggunaTerbaru ?? [] as $user)
                        <tr class="hover:bg-rimba-50/50 transition">
                            <td class="py-3 px-4 font-medium text-ink-900 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full bg-rimba-100 text-rimba-700 text-[10px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                                </span>
                                <span>{{ $user->nama_lengkap }}</span>
                            </td>
                            <td class="py-3 px-4 text-ink-500">{{ $user->email }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-md bg-rimba-100 px-2 py-0.5 text-[11px] font-medium text-rimba-700">
                                    {{ $user->peran?->nama_peran ?? 'Tanpa Peran' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($user->isAktif())
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold text-[11px]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-600 font-semibold text-[11px]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-ink-400">
                                {{ $user->dibuat_pada ? \Carbon\Carbon::parse($user->dibuat_pada)->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-ink-400">
                                Belum ada data pengguna terdaftar di database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
