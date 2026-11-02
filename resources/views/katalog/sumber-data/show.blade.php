@extends('layouts.app')

@section('title', $sumberData->nama_sumber.' — RimbaWatch')
@section('breadcrumb', 'Sumber Data / Detail')

@section('content')
    @php($metadataKurang = $sumberData->labelMetadataBelumLengkap())

    <div class="mb-8">
        <a href="{{ route('katalog.sumber-data.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke katalog
        </a>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-03 · Admin</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">{{ $sumberData->nama_sumber }}</h1>

                <div class="mt-3 flex flex-wrap gap-2">
                    <x-ui.badge :variant="$sumberData->is_aktif ? 'success' : 'danger'">
                        {{ $sumberData->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </x-ui.badge>
                    <x-ui.badge :variant="$sumberData->jenis_sumber === \App\Models\SumberData::JENIS_INTERNAL ? 'info' : 'neutral'">
                        {{ ucfirst(strtolower($sumberData->jenis_sumber)) }}
                    </x-ui.badge>
                    <x-ui.badge variant="neutral">{{ $sumberData->status_kualitas?->label() ?? 'Belum diketahui' }}</x-ui.badge>
                </div>
            </div>

            <a href="{{ route('katalog.sumber-data.edit', $sumberData) }}" class="btn-secondary shrink-0">Ubah metadata</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-lg font-semibold text-ink-900">Metadata dataset</h2>

                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Jenis sumber</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->jenis_sumber ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Nama penyedia</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->nama_penyedia }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">URL sumber</dt>
                        <dd class="mt-1 break-all text-sm">
                            @if ($sumberData->url_sumber)
                                <a href="{{ $sumberData->url_sumber }}" target="_blank" rel="noopener noreferrer" class="font-medium text-rimba-600 hover:text-rimba-700 hover:underline">
                                    {{ $sumberData->url_sumber }}
                                </a>
                            @else
                                <span class="text-ink-400">-</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Nama lisensi</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->nama_lisensi }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Format data</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->format_data }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Deskripsi cakupan</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->deskripsi_cakupan ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Periode data</dt>
                        <dd class="mt-1 text-sm text-ink-700">
                            {{ $sumberData->periode_mulai?->format('d M Y') ?? '-' }}
                            <span class="text-ink-400">s.d.</span>
                            {{ $sumberData->periode_selesai?->format('d M Y') ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Sistem koordinat (CRS)</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->kode_crs }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Interval penyegaran</dt>
                        <dd class="mt-1 text-sm text-ink-700">
                            {{ $sumberData->interval_penyegaran_jam !== null ? $sumberData->interval_penyegaran_jam.' jam' : '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-ink-400">Status kualitas</dt>
                        <dd class="mt-1 text-sm text-ink-700">{{ $sumberData->status_kualitas?->label() ?? '-' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-lg font-semibold text-ink-900">Riwayat perubahan</h2>
                <p class="mt-1 text-sm text-ink-400">Perubahan metadata tercatat dan tidak menimpa riwayat sebelumnya.</p>

                <ol class="mt-5 space-y-5">
                    @forelse ($sumberData->riwayat as $riwayat)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-rimba-600" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink-900">
                                    {{ $riwayat->aksi->label() }}
                                    <span class="font-normal text-ink-400">· {{ $riwayat->dibuat_pada?->format('d M Y • H:i') }}</span>
                                </p>
                                <p class="mt-0.5 text-xs text-ink-400">oleh {{ $riwayat->pengguna?->nama_lengkap ?? 'Sistem' }}</p>

                                @if (! empty($riwayat->perubahan))
                                    <ul class="mt-2 space-y-1 text-xs text-ink-500">
                                        @foreach ($riwayat->perubahan as $field => $nilai)
                                            <li>
                                                <span class="font-medium text-ink-700">{{ $field }}</span>:
                                                {{ is_array($nilai) ? ($nilai['lama'] ?? '-') : $nilai }}
                                                &rarr;
                                                {{ is_array($nilai) ? ($nilai['baru'] ?? '-') : $nilai }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($riwayat->catatan)
                                    <p class="mt-2 text-xs italic text-ink-400">{{ $riwayat->catatan }}</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-400">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Status &amp; aksi</h2>
                <p class="mt-3 text-sm text-ink-500">
                    Dataset saat ini
                    <strong class="font-semibold text-ink-900">{{ $sumberData->is_aktif ? 'aktif' : 'nonaktif' }}</strong>.
                </p>

                @if ($sumberData->is_aktif)
                    <form method="POST" action="{{ route('katalog.sumber-data.nonaktifkan', $sumberData) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="catatan" class="field-label">Alasan penonaktifan</label>
                            <input id="catatan" name="catatan" type="text" maxlength="500" placeholder="Opsional" class="field-input">
                        </div>

                        <button type="submit" class="btn-danger w-full">Nonaktifkan dataset</button>
                    </form>
                @elseif ($metadataKurang !== [])
                    <div class="mt-4 rounded-xl border border-amber-ink/20 bg-amber-soft px-4 py-3 text-sm text-amber-ink" role="status">
                        <p class="font-semibold">Belum bisa diaktifkan</p>
                        <p class="mt-1">Lengkapi metadata wajib berikut:</p>
                        <ul class="mt-2 list-inside list-disc space-y-0.5">
                            @foreach ($metadataKurang as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <button
                        type="button"
                        class="btn-primary mt-4 w-full cursor-not-allowed opacity-50"
                        disabled
                        aria-disabled="true"
                        title="Lengkapi metadata wajib terlebih dahulu"
                    >
                        Aktifkan dataset
                    </button>

                    <a href="{{ route('katalog.sumber-data.edit', $sumberData) }}" class="btn-secondary mt-2 w-full">
                        Lengkapi metadata
                    </a>
                @else
                    <form method="POST" action="{{ route('katalog.sumber-data.aktifkan', $sumberData) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary w-full">Aktifkan dataset</button>
                    </form>
                @endif
            </x-ui.card>

            <x-ui.card class="p-6">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Keterlacakan</h2>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-ink-400">Dibuat oleh</dt>
                        <dd class="mt-0.5 font-medium text-ink-700">{{ $sumberData->pembuat?->nama_lengkap ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Dibuat pada</dt>
                        <dd class="mt-0.5 text-ink-700">{{ $sumberData->dibuat_pada?->format('d M Y • H:i') ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Metadata terakhir diperbarui</dt>
                        <dd class="mt-0.5 text-ink-700">{{ $sumberData->diperbarui_pada?->format('d M Y • H:i') ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Data terakhir diambil dari sumber</dt>
                        <dd class="mt-0.5 text-ink-700">
                            {{ $sumberData->terakhir_diambil_pada?->format('d M Y • H:i') ?? 'Belum pernah diambil' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-ink-400">Status kebaruan data</dt>
                        <dd class="mt-1">
                            <x-ui.badge :variant="$sumberData->status_kebaruan === 'FRESH' ? 'success' : ($sumberData->status_kebaruan === 'UNKNOWN' ? 'neutral' : 'warning')">
                                {{ $sumberData->status_kebaruan === 'UNKNOWN' ? 'Belum diketahui' : $sumberData->status_kebaruan }}
                            </x-ui.badge>
                        </dd>
                    </div>
                </dl>
            </x-ui.card>
        </aside>
    </div>
@endsection
