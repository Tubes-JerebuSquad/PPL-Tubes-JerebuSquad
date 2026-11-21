@extends('layouts.app')

@section('title', 'Laporan #'.$laporan->kode_laporan.' — RimbaWatch')
@section('breadcrumb', 'Laporan Saya / #'.$laporan->kode_laporan)

@section('content')
    @php
        $catatanPengawas = $laporan->catatanPerbaikanTerbaru();
        $revisiTerbaru = $laporan->revisi->first();
    @endphp

    <div class="mb-8">
        <a href="{{ route('masyarakat.laporan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke Laporan Saya
        </a>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-09 · Pelacakan Laporan</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">
                    Laporan #{{ $laporan->kode_laporan }}
                </h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <x-ui.badge :variant="$laporan->varianStatus()">
                        {{ $laporan->labelStatus() }}
                    </x-ui.badge>
                    <x-ui.badge variant="neutral">
                        {{ $laporan->labelKategori() }}
                    </x-ui.badge>
                    <span class="text-xs text-ink-400">
                        Diperbarui {{ $laporan->diperbarui_pada?->format('d M Y • H:i') ?? $laporan->dibuat_pada?->format('d M Y • H:i') ?? '-' }}
                    </span>
                </div>
            </div>

            @if ($laporan->bisaDiubahPelapor())
                <a href="{{ route('masyarakat.laporan.edit', $laporan) }}" class="btn-primary shrink-0">
                    Perbaiki &amp; kirim ulang
                </a>
            @endif
        </div>
    </div>

    @if ($laporan->bisaDiubahPelapor())
        <div class="mb-6 rounded-xl border border-amber-ink/20 bg-amber-soft px-5 py-4 text-sm text-amber-ink" role="status">
            <p class="font-semibold">Laporan dikembalikan untuk perbaikan</p>
            <p class="mt-1">
                {{ $catatanPengawas ?: 'Silakan lengkapi atau perbaiki informasi laporan Anda, lalu kirim ulang untuk ditinjau kembali.' }}
            </p>
        </div>
    @else
        <div class="mb-6 rounded-xl border border-line bg-white px-5 py-3.5 text-sm text-ink-500">
            Laporan berstatus <strong class="text-ink-900">{{ $laporan->labelStatus() }}</strong> sudah terkunci dalam tahap pemeriksaan/penanganan dan tidak dapat diubah.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-lg font-semibold text-ink-900">Informasi laporan terkini</h2>
                <p class="mt-1 text-xs text-ink-400">Laporan masyarakat merupakan informasi awal yang diverifikasi oleh Pengawas.</p>

                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Kategori</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $laporan->labelKategori() }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Waktu kejadian</dt>
                        <dd class="mt-1 text-sm text-ink-700">
                            {{ $laporan->insiden_pada?->format('d M Y • H:i') ?? 'Tidak dicantumkan' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Wilayah</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $laporan->wilayah?->nama_wilayah ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Kawasan</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $laporan->kawasan?->nama_kawasan ?? 'Tidak ditentukan' }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Koordinat lokasi</dt>
                        <dd class="mt-1 font-mono text-sm text-ink-700">
                            @if ($koordinat['latitude'] !== null && $koordinat['longitude'] !== null)
                                {{ number_format((float) $koordinat['latitude'], 6) }}, {{ number_format((float) $koordinat['longitude'], 6) }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Uraian kejadian</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-ink-700">{{ $laporan->deskripsi }}</dd>
                    </div>
                </dl>

                @if ($revisiTerbaru && $revisiTerbaru->berkasMedia->isNotEmpty())
                    <div class="mt-6 border-t border-line pt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-400">Lampiran bukti terkini</h3>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($revisiTerbaru->berkasMedia as $media)
                                <a href="{{ route('masyarakat.laporan.media', [$laporan, $media]) }}" target="_blank" class="group block overflow-hidden rounded-lg border border-line bg-rimba-50">
                                    @if ($media->isFoto())
                                        <img
                                            src="{{ route('masyarakat.laporan.media', [$laporan, $media]) }}"
                                            alt="{{ $media->nama_file_asli }}"
                                            loading="lazy"
                                            class="h-28 w-full object-cover transition group-hover:scale-105"
                                        >
                                    @else
                                        <div class="flex h-28 items-center justify-center bg-rimba-100 text-xs font-semibold text-rimba-700">
                                            Video lampiran
                                        </div>
                                    @endif
                                    <p class="truncate px-2.5 py-1.5 text-xs text-ink-500">{{ $media->nama_file_asli }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-lg font-semibold text-ink-900">Riwayat revisi laporan</h2>
                <p class="mt-1 text-sm text-ink-400">Setiap pengiriman perbaikan tersimpan sebagai versi baru tanpa menimpa versi sebelumnya.</p>

                <div class="mt-5 space-y-4">
                    @forelse ($laporan->revisi as $revisi)
                        <article class="rounded-xl border border-line p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-ink-900">
                                    Revisi #{{ $revisi->nomor_revisi }} · {{ $revisi->labelKategori() }}
                                </p>
                                <span class="text-xs text-ink-400">
                                    {{ $revisi->dibuat_pada?->format('d M Y • H:i') ?? '-' }}
                                </span>
                            </div>

                            <p class="mt-2 whitespace-pre-line text-sm text-ink-500">{{ $revisi->deskripsi }}</p>

                            @if ($revisi->alasan_revisi)
                                <p class="mt-2 text-xs italic text-ink-400">Catatan pelapor: {{ $revisi->alasan_revisi }}</p>
                            @endif

                            @if ($revisi->berkasMedia->isNotEmpty())
                                <p class="mt-2 text-xs text-ink-400">Lampiran: {{ $revisi->berkasMedia->count() }} berkas</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-ink-400">Belum ada catatan versi revisi.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Riwayat perubahan status</h2>
                <p class="mt-1 text-xs text-ink-400">Jejak status dan waktu setiap pembaruan laporan.</p>

                <ol class="mt-5 space-y-5">
                    @forelse ($laporan->peristiwa as $peristiwa)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-rimba-600" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink-900">{{ $peristiwa->labelJenis() }}</p>
                                <p class="mt-0.5 text-xs text-ink-400">
                                    {{ $peristiwa->terjadi_pada?->format('d M Y • H:i') ?? '-' }}
                                </p>

                                @if ($peristiwa->labelStatusLama() || $peristiwa->labelStatusBaru())
                                    <p class="mt-1.5 text-xs font-medium text-ink-700">
                                        {{ $peristiwa->labelStatusLama() ?? 'Awal' }}
                                        &rarr;
                                        {{ $peristiwa->labelStatusBaru() ?? '-' }}
                                    </p>
                                @endif

                                @if ($peristiwa->catatan_peristiwa)
                                    @php
                                        $isPengembalian = in_array(strtoupper((string) $peristiwa->jenis_peristiwa), ['PERMINTAAN_PERBAIKAN', 'DIKEMBALIKAN'], true)
                                            || strtoupper((string) $peristiwa->status_baru) === \App\Models\Laporan::STATUS_DIKEMBALIKAN;
                                    @endphp
                                    <p @class([
                                        'mt-1.5 text-xs',
                                        'rounded-lg bg-amber-soft px-2.5 py-2 text-amber-ink' => $isPengembalian,
                                        'text-ink-500' => ! $isPengembalian,
                                    ])>
                                        {{ $peristiwa->catatan_peristiwa }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-400">
                            Status saat ini: <strong class="text-ink-700">{{ $laporan->labelStatus() }}</strong>
                            ({{ $laporan->diperbarui_pada?->format('d M Y • H:i') ?? $laporan->dibuat_pada?->format('d M Y • H:i') ?? '-' }}).
                        </li>
                    @endforelse
                </ol>
            </x-ui.card>
        </aside>
    </div>
@endsection
