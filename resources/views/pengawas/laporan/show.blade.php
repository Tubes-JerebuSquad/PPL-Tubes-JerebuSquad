@extends('layouts.app')

@section('title', 'Tinjau Laporan '.$laporan->kode_laporan)
@section('breadcrumb', 'Pengawas / Verifikasi Laporan / '.$laporan->kode_laporan)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">Laporan {{ $laporan->kode_laporan }}</h1>
            <p class="text-sm opacity-70">Informasi awal dari pelapor. Belum terkonfirmasi sebelum diperiksa.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.status-laporan :status="$laporan->status_laporan" />
            <a href="{{ route('pengawas.laporan.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-rimba-50 px-4 py-3 text-sm font-medium text-rimba-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card class="p-5">
                <h2 class="text-base font-semibold">Detail laporan</h2>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kategori dilaporkan</dt>
                        <dd>{{ $laporan->labelKategori() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Wilayah</dt>
                        <dd>{{ $laporan->wilayah?->nama_wilayah ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kawasan terkait</dt>
                        <dd>{{ $laporan->kawasan?->nama_kawasan ?? 'Belum dikaitkan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Waktu kejadian</dt>
                        <dd>{{ $laporan->insiden_pada?->translatedFormat('d M Y H:i') ?? 'Tidak diketahui' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Pelapor</dt>
                        <dd>{{ $laporan->pelapor?->nama_lengkap ?? 'Tidak diketahui' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Dikirim pada</dt>
                        <dd>{{ $laporan->dibuat_pada?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Uraian</dt>
                        <dd class="whitespace-pre-line">{{ $laporan->deskripsi }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card class="p-5">
                <h2 class="text-base font-semibold">Riwayat laporan</h2>
                @forelse ($laporan->peristiwa as $p)
                    <div class="mt-3 border-l-2 border-rimba-100 pl-3 text-sm">
                        <p class="font-medium">
                            {{ $p->labelJenis() }}
                            @if ($p->status_lama || $p->status_baru)
                                <span class="font-normal text-ink-500">
                                    ({{ $p->labelStatusLama() ?? '-' }} → {{ $p->labelStatusBaru() ?? '-' }})
                                </span>
                            @endif
                        </p>
                        <p class="text-xs text-ink-400">
                            {{ $p->aktor?->nama_lengkap ?? 'Sistem' }} · {{ $p->terjadi_pada?->translatedFormat('d M Y H:i') }}
                        </p>
                        @if ($p->catatan_peristiwa)
                            <p class="mt-1 whitespace-pre-line">{{ $p->catatan_peristiwa }}</p>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm opacity-70">Belum ada riwayat.</p>
                @endforelse
            </x-ui.card>
        </div>

        <div>
            <x-ui.card class="p-5">
                <h2 class="text-base font-semibold">Keputusan triase</h2>

                @if (! $laporan->bisaDitriase())
                    <div class="mt-3 space-y-2 text-sm">
                        <p class="opacity-70">Laporan ini sudah diputuskan dan tidak dapat ditriase ulang.</p>
                        @if ($laporan->kategori_triase)
                            <p><span class="font-medium">Kategori triase:</span> {{ \App\Models\Laporan::KATEGORI_TRIASE[$laporan->kategori_triase] ?? $laporan->kategori_triase }}</p>
                        @endif
                        @if ($laporan->prioritas_triase)
                            <p><span class="font-medium">Prioritas:</span> {{ \App\Models\Laporan::PRIORITAS_TRIASE[$laporan->prioritas_triase] ?? $laporan->prioritas_triase }}</p>
                        @endif
                        @if ($laporan->catatan_publik)
                            <p><span class="font-medium">Catatan untuk pelapor:</span> {{ $laporan->catatan_publik }}</p>
                        @endif
                        @if ($laporan->catatan_internal)
                            <p><span class="font-medium">Catatan internal:</span> {{ $laporan->catatan_internal }}</p>
                        @endif
                        @if ($laporan->penriase)
                            <p class="text-xs text-ink-400">Ditriase oleh {{ $laporan->penriase->nama_lengkap }} pada {{ $laporan->ditriase_pada?->translatedFormat('d M Y H:i') }}</p>
                        @endif
                    </div>
                @else
                    <form method="POST" action="{{ route('pengawas.laporan.triase', $laporan) }}" class="mt-3 grid gap-4">
                        @csrf

                        <fieldset>
                            <legend class="field-label">Keputusan</legend>
                            @foreach ([
                                \App\Models\Laporan::TINDAKAN_TERIMA           => 'Terima untuk inspeksi',
                                \App\Models\Laporan::TINDAKAN_MINTA_PERBAIKAN  => 'Minta perbaikan',
                                \App\Models\Laporan::TINDAKAN_TOLAK            => 'Tolak laporan',
                            ] as $nilai => $label)
                                <label class="mb-1.5 flex items-center gap-2 text-sm">
                                    <input type="radio" name="tindakan" value="{{ $nilai }}" @checked(old('tindakan') === $nilai)>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                            @error('tindakan')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div>
                            <label for="kategori_triase" class="field-label">Kategori triase</label>
                            <select id="kategori_triase" name="kategori_triase" class="field-input">
                                <option value="">Belum diklasifikasikan</option>
                                @foreach (\App\Models\Laporan::KATEGORI_TRIASE as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('kategori_triase') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori_triase')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="prioritas_triase" class="field-label">Prioritas</label>
                            <select id="prioritas_triase" name="prioritas_triase" class="field-input">
                                <option value="">Belum ditentukan</option>
                                @foreach (\App\Models\Laporan::PRIORITAS_TRIASE as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('prioritas_triase') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('prioritas_triase')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="catatan_publik" class="field-label">Alasan / catatan untuk pelapor</label>
                            <textarea id="catatan_publik" name="catatan_publik" rows="3" maxlength="2000" class="field-input"
                                      aria-describedby="bantuan-catatan-publik">{{ old('catatan_publik') }}</textarea>
                            <p id="bantuan-catatan-publik" class="mt-1 text-xs text-ink-400">
                                Wajib diisi untuk Minta perbaikan dan Tolak. Catatan ini dapat dilihat pelapor.
                            </p>
                            @error('catatan_publik')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="catatan_internal" class="field-label">Catatan internal (opsional)</label>
                            <textarea id="catatan_internal" name="catatan_internal" rows="2" maxlength="2000" class="field-input">{{ old('catatan_internal') }}</textarea>
                            <p class="mt-1 text-xs text-ink-400">Hanya terlihat oleh Pengawas.</p>
                            @error('catatan_internal')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary">Simpan keputusan</button>
                    </form>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
