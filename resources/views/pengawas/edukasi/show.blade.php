@extends('layouts.app')

@section('title', $materi->judul.' — RimbaWatch')
@section('breadcrumb', 'Materi Edukasi / Detail')

@section('content')
    <div class="mb-8">
        <a href="{{ route('pengawas.edukasi.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke daftar materi
        </a>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-16 · Pengawas</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">{{ $materi->judul }}</h1>

                <div class="mt-3 flex flex-wrap gap-2">
                    <x-ui.badge :variant="$materi->status_publikasi->variant()">
                        {{ $materi->status_publikasi->label() }}
                    </x-ui.badge>
                    <x-ui.badge variant="info">{{ $materi->kategori }}</x-ui.badge>
                </div>
            </div>

            <a href="{{ route('pengawas.edukasi.edit', $materi) }}" class="btn-secondary shrink-0">Ubah materi</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-lg font-semibold text-ink-900">Isi materi</h2>
                <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-ink-700">{{ $materi->konten }}</div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Sumber rujukan</h2>
                <p class="mt-3 whitespace-pre-line break-words text-sm text-ink-700">
                    {{ $materi->referensi_sumber ?? '-' }}
                </p>
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Status &amp; publikasi</h2>
                <p class="mt-3 text-sm text-ink-500">
                    Status saat ini:
                    <strong class="font-semibold text-ink-900">{{ $materi->status_publikasi->label() }}</strong>.
                </p>

                @if ($materi->masihDraf())
                    <p class="mt-2 text-xs text-ink-400">
                        Materi berstatus draf tidak tampil pada halaman publik.
                    </p>

                    <div class="mt-4 space-y-2.5">
                        <form method="POST" action="{{ route('pengawas.edukasi.terbitkan', $materi) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-primary w-full">Terbitkan materi</button>
                        </form>

                        <form method="POST" action="{{ route('pengawas.edukasi.arsipkan', $materi) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-secondary w-full">Arsipkan materi</button>
                        </form>
                    </div>
                @elseif ($materi->sudahTerbit())
                    <p class="mt-2 text-xs text-ink-400">
                        Materi ini sedang tayang dan dapat dibaca oleh masyarakat pada halaman publik.
                    </p>

                    <div class="mt-4 space-y-2.5">
                        <a href="{{ route('edukasi.show', $materi) }}" class="btn-secondary w-full">
                            Lihat di halaman publik
                        </a>

                        <form
                            method="POST"
                            action="{{ route('pengawas.edukasi.arsipkan', $materi) }}"
                            onsubmit="return confirm('Arsipkan materi ini? Materi tidak akan tampil pada daftar publik.');"
                        >
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-danger w-full">Arsipkan materi</button>
                        </form>
                    </div>
                @else
                    <p class="mt-2 text-xs text-ink-400">
                        Materi yang diarsipkan tidak tampil pada daftar publik.
                    </p>

                    <form method="POST" action="{{ route('pengawas.edukasi.terbitkan', $materi) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary w-full">Terbitkan kembali</button>
                    </form>
                @endif
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Informasi pencatatan</h2>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-ink-400">Dibuat oleh</dt>
                        <dd class="mt-0.5 font-medium text-ink-700">{{ $materi->pembuat?->nama_lengkap ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Dibuat pada</dt>
                        <dd class="mt-0.5 text-ink-700">{{ $materi->dibuat_pada?->format('d M Y • H:i') ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Terakhir diperbarui oleh</dt>
                        <dd class="mt-0.5 font-medium text-ink-700">{{ $materi->pembaru?->nama_lengkap ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Terakhir diperbarui</dt>
                        <dd class="mt-0.5 text-ink-700">{{ $materi->diperbarui_pada?->format('d M Y • H:i') ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Tanggal diterbitkan</dt>
                        <dd class="mt-0.5 text-ink-700">{{ $materi->dipublikasikan_pada?->format('d M Y • H:i') ?? 'Belum diterbitkan' }}</dd>
                    </div>

                    @if ($materi->diarsipkan_pada)
                        <div>
                            <dt class="text-xs text-ink-400">Tanggal diarsipkan</dt>
                            <dd class="mt-0.5 text-ink-700">{{ $materi->diarsipkan_pada->format('d M Y • H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>
        </aside>
    </div>
@endsection
