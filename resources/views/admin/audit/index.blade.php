@extends('layouts.app')

@section('title', 'Riwayat Perubahan')
@section('breadcrumb', 'Admin / Pengguna & Akses / Riwayat Perubahan')

@php
    $warnaAksi = [
        'AKUN_DIBUAT' => 'success',
        'DATA_DIUBAH' => 'info',
        'PERAN_DIUBAH' => 'warning',
        'AKUN_DINONAKTIFKAN' => 'danger',
        'AKUN_DIAKTIFKAN' => 'success',
    ];

    $labelKolom = [
        'nama_lengkap' => 'Nama',
        'email' => 'Email',
        'telepon' => 'Telepon',
        'peran_id' => 'Peran (ID)',
        'kata_sandi' => 'Kata sandi',
    ];
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">Riwayat Perubahan</h1>
            <p class="text-sm opacity-70">Jejak perubahan akun dan peran pengguna.</p>
        </div>
        <a href="{{ route('admin.pengguna.index') }}" class="btn-secondary">Kembali ke Pengguna</a>
    </div>

    <x-ui.card class="mt-4">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="grid gap-3 sm:grid-cols-3">
            <div>
                <label for="aksi" class="field-label">Jenis perubahan</label>
                <select id="aksi" name="aksi" class="field-input">
                    <option value="">Semua</option>
                    @foreach ($labelAksi as $kode => $label)
                        <option value="{{ $kode }}" @selected($aksiDipilih === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Terapkan</button>
                <a href="{{ route('admin.audit.index') }}" class="btn-secondary">Atur ulang</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4">
        @if ($daftar->isEmpty())
            <p class="py-6 text-center text-sm opacity-70">Belum ada riwayat perubahan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 pr-4">Waktu</th>
                            <th class="py-2 pr-4">Pelaku</th>
                            <th class="py-2 pr-4">Akun</th>
                            <th class="py-2 pr-4">Perubahan</th>
                            <th class="py-2">Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $item)
                            <tr class="border-b align-top last:border-0">
                                <td class="whitespace-nowrap py-3 pr-4">{{ $item->dibuat_pada->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-4">{{ $item->pelaku?->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 pr-4">{{ $item->sasaran?->nama_lengkap ?? 'Akun dihapus' }}</td>
                                <td class="py-3 pr-4">
                                    <x-ui.badge variant="{{ $warnaAksi[$item->aksi] ?? 'neutral' }}">
                                        {{ $labelAksi[$item->aksi] ?? $item->aksi }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3">
                                    @forelse ($item->perubahan ?? [] as $kolom => $nilai)
                                        <div>
                                            <span class="font-medium">{{ $labelKolom[$kolom] ?? $kolom }}:</span>
                                            @if (is_array($nilai))
                                                {{ $nilai['dari'] ?? '-' }} &rarr; {{ $nilai['menjadi'] ?? '-' }}
                                            @else
                                                {{ $nilai }}
                                            @endif
                                        </div>
                                    @empty
                                        <span class="opacity-60">-</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $daftar->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection