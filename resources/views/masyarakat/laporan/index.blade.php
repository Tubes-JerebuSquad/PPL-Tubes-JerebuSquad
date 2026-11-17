@extends('layouts.app')

@section('title', 'Laporan Saya — RimbaWatch')
@section('breadcrumb', 'Laporan Saya')

@section('content')
    @php
        $adaFilter = filled(request('cari')) || filled(request('status'));
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-09 · Masyarakat</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">Laporan Saya</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-500">
                Pantau status pemeriksaan, riwayat perubahan, dan perbaiki laporan yang dikembalikan untuk ditinjau ulang.
            </p>
        </div>

        <a href="{{ route('masyarakat.dashboard') }}" class="btn-secondary shrink-0">
            Kembali ke dashboard
        </a>
    </div>

    <x-ui.card class="overflow-hidden">
        <form method="GET" action="{{ route('masyarakat.laporan.index') }}" class="flex flex-col gap-3 border-b border-line p-5 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="cari" class="field-label">Cari laporan</label>
                <input
                    id="cari"
                    name="cari"
                    type="search"
                    value="{{ old('cari', request('cari')) }}"
                    placeholder="Nomor laporan, kategori, atau uraian..."
                    class="field-input"
                    @error('cari') aria-invalid="true" aria-describedby="cari-error" @enderror
                >
                <x-ui.field-error name="cari" />
            </div>

            <div class="md:w-56">
                <label for="status" class="field-label">Status</label>
                <select
                    id="status"
                    name="status"
                    class="field-input"
                    @error('status') aria-invalid="true" aria-describedby="status-error" @enderror
                >
                    <option value="">Semua status</option>
                    @foreach ($pilihanStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('status', request('status')) === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <x-ui.field-error name="status" />
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Terapkan</button>

                @if ($adaFilter)
                    <a href="{{ route('masyarakat.laporan.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($daftarLaporan->isEmpty())
            <div class="px-5 py-14">
                <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rimba-100 text-rimba-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                        </svg>
                    </span>

                    <p class="mt-4 text-sm font-semibold text-ink-900">Belum ada laporan.</p>

                    @if ($adaFilter)
                        <p class="mt-1 text-sm text-ink-400">Tidak ada laporan Anda yang cocok dengan filter saat ini.</p>
                        <a href="{{ route('masyarakat.laporan.index') }}" class="btn-secondary mt-4">Reset filter</a>
                    @else
                        <p class="mt-1 text-sm text-ink-400">
                            Laporan indikasi kerusakan kawasan yang Anda ajukan akan tampil di halaman ini.
                        </p>
                    @endif
                </div>
            </div>
        @else
            {{-- Mobile & tablet: card list agar tidak perlu scroll horizontal --}}
            <ul class="divide-y divide-line lg:hidden" aria-label="Daftar laporan saya">
                @foreach ($daftarLaporan as $laporan)
                    <li class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-xs text-ink-400">#{{ $laporan->nomor_laporan }}</p>
                                <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="mt-0.5 block font-semibold text-ink-900 hover:text-rimba-600">
                                    {{ $laporan->labelKategori() }}
                                </a>
                                <p class="mt-0.5 text-xs text-ink-400">{{ $laporan->wilayah?->nama_wilayah ?? '-' }}</p>
                            </div>

                            <x-ui.badge :variant="$laporan->varianStatus()">
                                {{ $laporan->labelStatus() }}
                            </x-ui.badge>
                        </div>

                        <p class="mt-2 line-clamp-2 text-xs text-ink-500">{{ $laporan->deskripsi }}</p>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3 text-xs">
                            <span class="text-ink-400">
                                Diperbarui: {{ $laporan->updated_at?->format('d M Y • H:i') ?? '-' }}
                            </span>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="font-semibold text-rimba-600 hover:text-rimba-700">
                                    Detail
                                </a>
                                @if ($laporan->bolehDiperbaiki())
                                    <a href="{{ route('masyarakat.laporan.edit', $laporan) }}" class="font-semibold text-amber-ink hover:underline">
                                        Perbaiki
                                    </a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Desktop: tabel --}}
            <div class="hidden lg:block">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Daftar laporan saya</caption>
                    <thead>
                        <tr class="border-b border-line text-[11px] uppercase tracking-[0.12em] text-ink-400">
                            <th scope="col" class="px-4 py-3 font-semibold">Nomor &amp; Kategori</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Wilayah</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Perubahan Terakhir</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($daftarLaporan as $laporan)
                            <tr class="transition hover:bg-rimba-50/70">
                                <td class="px-4 py-4">
                                    <p class="font-mono text-xs text-ink-400">#{{ $laporan->nomor_laporan }}</p>
                                    <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="mt-0.5 block font-semibold text-ink-900 hover:text-rimba-600">
                                        {{ $laporan->labelKategori() }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-ink-500">
                                    {{ $laporan->wilayah?->nama_wilayah ?? '-' }}
                                    @if ($laporan->kawasan)
                                        <p class="mt-0.5 text-xs text-ink-400">{{ $laporan->kawasan->nama_kawasan }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <x-ui.badge :variant="$laporan->varianStatus()">
                                        {{ $laporan->labelStatus() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-4 text-ink-500">
                                    {{ $laporan->updated_at?->format('d M Y • H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="btn-secondary">Detail</a>
                                        @if ($laporan->bolehDiperbaiki())
                                            <a href="{{ route('masyarakat.laporan.edit', $laporan) }}" class="btn-primary">Perbaiki</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($daftarLaporan->hasPages())
            <div class="border-t border-line px-5 py-4">
                {{ $daftarLaporan->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection
