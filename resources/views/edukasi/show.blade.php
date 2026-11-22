@extends('layouts.app')

@section('title', $materi->judul.' — RimbaWatch')
@section('breadcrumb', 'Materi Edukasi / Baca')

@guest
    @section('pengguna_inisial', 'PB')
    @section('pengguna_nama', 'Pengunjung Publik')
    @section('pengguna_peran', 'Publik')
@endguest

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('edukasi.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke materi edukasi
        </a>

        <article class="mt-5 space-y-6">
            <x-ui.card class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2.5">
                    <x-ui.badge variant="info">{{ $materi->kategori }}</x-ui.badge>
                    <span class="text-xs text-ink-400">
                        Diterbitkan {{ $materi->tanggalTampil()?->format('d M Y • H:i') ?? '-' }}
                    </span>
                </div>

                <h1 class="mt-4 text-2xl font-bold tracking-tight text-rimba-900 sm:text-3xl">
                    {{ $materi->judul }}
                </h1>

                <div class="mt-6 whitespace-pre-line text-sm leading-7 text-ink-700 sm:text-base">{{ $materi->konten }}</div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Sumber rujukan</h2>
                <p class="mt-2.5 whitespace-pre-line break-words text-sm text-ink-700">
                    {{ $materi->referensi_sumber ?? '-' }}
                </p>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-xs text-ink-400">
                    <span>Disusun oleh: {{ $materi->pembuat?->nama_lengkap ?? 'Pengawas RimbaWatch' }}</span>
                    <span>Diperbarui: {{ $materi->diperbarui_pada?->format('d M Y') ?? $materi->tanggalTampil()?->format('d M Y') ?? '-' }}</span>
                </div>
            </x-ui.card>
        </article>
    </div>
@endsection
