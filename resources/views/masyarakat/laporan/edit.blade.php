@extends('layouts.app')

@section('title', 'Perbaiki Laporan #'.$laporan->nomor_laporan.' — RimbaWatch')
@section('breadcrumb', 'Laporan Saya / Perbaiki')

@section('content')
    @php
        $catatanPengawas = $laporan->catatanPerbaikanTerbaru();
        $revisiTerbaru = $laporan->revisiTerbaru;
        $fotoSebelumnya = $revisiTerbaru?->foto ?? collect();
    @endphp

    <div class="mb-8">
        <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke detail laporan
        </a>

        <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-09 · Perbaikan Laporan</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">
            Perbaiki Laporan #{{ $laporan->nomor_laporan }}
        </h1>
        <p class="mt-2 max-w-2xl text-sm text-ink-500">
            Lengkapi atau perbaiki data laporan Anda di bawah ini, lalu kirim ulang untuk ditinjau kembali oleh Pengawas.
        </p>
    </div>

    @if ($catatanPengawas)
        <div class="mb-6 rounded-xl border border-amber-ink/20 bg-amber-soft px-5 py-4 text-sm text-amber-ink" role="status">
            <p class="font-semibold">Catatan perbaikan dari Pengawas</p>
            <p class="mt-1">{{ $catatanPengawas }}</p>
        </div>
    @endif

    <x-ui.card class="p-6 sm:p-8">
        <form
            method="POST"
            action="{{ route('masyarakat.laporan.update', $laporan) }}"
            enctype="multipart/form-data"
            class="space-y-8"
        >
            @csrf
            @method('PUT')

            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Kategori &amp; lokasi kejadian</h2>

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="kategori" class="field-label">Kategori indikasi <span class="text-danger-ink">*</span></label>
                        <select
                            id="kategori"
                            name="kategori"
                            class="field-input"
                            required
                            @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror
                        >
                            @foreach ($daftarKategori as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('kategori', strtolower((string) $laporan->kategori)) === $nilai)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-ui.field-error name="kategori" />
                    </div>

                    <div>
                        <label for="waktu_kejadian" class="field-label">Waktu kejadian / perkiraan</label>
                        <input
                            id="waktu_kejadian"
                            name="waktu_kejadian"
                            type="datetime-local"
                            value="{{ old('waktu_kejadian', $laporan->waktu_kejadian?->format('Y-m-d\TH:i')) }}"
                            class="field-input"
                            @error('waktu_kejadian') aria-invalid="true" aria-describedby="waktu_kejadian-error" @enderror
                        >
                        <x-ui.field-error name="waktu_kejadian" />
                    </div>

                    <div>
                        <label for="wilayah_id" class="field-label">Wilayah <span class="text-danger-ink">*</span></label>
                        <select
                            id="wilayah_id"
                            name="wilayah_id"
                            class="field-input"
                            required
                            @error('wilayah_id') aria-invalid="true" aria-describedby="wilayah_id-error" @enderror
                        >
                            <option value="">Pilih wilayah</option>
                            @foreach ($daftarWilayah as $wilayah)
                                <option value="{{ $wilayah->wilayah_id }}" @selected((int) old('wilayah_id', $laporan->wilayah_id) === (int) $wilayah->wilayah_id)>
                                    {{ $wilayah->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                        <x-ui.field-error name="wilayah_id" />
                    </div>

                    <div>
                        <label for="kawasan_id" class="field-label">Kawasan (opsional)</label>
                        <select
                            id="kawasan_id"
                            name="kawasan_id"
                            class="field-input"
                            @error('kawasan_id') aria-invalid="true" aria-describedby="kawasan_id-error" @enderror
                        >
                            <option value="">Tidak ditentukan</option>
                            @foreach ($daftarKawasan as $kawasan)
                                <option value="{{ $kawasan->kawasan_id }}" @selected((int) old('kawasan_id', $laporan->kawasan_id) === (int) $kawasan->kawasan_id)>
                                    {{ $kawasan->nama_kawasan }}
                                </option>
                            @endforeach
                        </select>
                        <x-ui.field-error name="kawasan_id" />
                    </div>

                    <div>
                        <label for="latitude" class="field-label">Latitude <span class="text-danger-ink">*</span></label>
                        <input
                            id="latitude"
                            name="latitude"
                            type="number"
                            step="any"
                            min="-90"
                            max="90"
                            value="{{ old('latitude', $laporan->latitude) }}"
                            class="field-input"
                            required
                            @error('latitude') aria-invalid="true" aria-describedby="latitude-error" @enderror
                        >
                        <x-ui.field-error name="latitude" />
                    </div>

                    <div>
                        <label for="longitude" class="field-label">Longitude <span class="text-danger-ink">*</span></label>
                        <input
                            id="longitude"
                            name="longitude"
                            type="number"
                            step="any"
                            min="-180"
                            max="180"
                            value="{{ old('longitude', $laporan->longitude) }}"
                            class="field-input"
                            required
                            @error('longitude') aria-invalid="true" aria-describedby="longitude-error" @enderror
                        >
                        <x-ui.field-error name="longitude" />
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Uraian &amp; catatan perbaikan</h2>

                <div class="mt-4 space-y-5">
                    <div>
                        <label for="deskripsi" class="field-label">Uraian kejadian <span class="text-danger-ink">*</span></label>
                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            rows="4"
                            maxlength="3000"
                            class="field-input"
                            required
                            @error('deskripsi') aria-invalid="true" aria-describedby="deskripsi-error" @enderror
                        >{{ old('deskripsi', $laporan->deskripsi) }}</textarea>
                        <x-ui.field-error name="deskripsi" />
                    </div>

                    <div>
                        <label for="catatan_revisi" class="field-label">Ringkasan perbaikan yang dilakukan (opsional)</label>
                        <textarea
                            id="catatan_revisi"
                            name="catatan_revisi"
                            rows="2"
                            maxlength="1000"
                            placeholder="Contoh: Menambahkan koordinat akurat dan foto kondisi lokasi..."
                            class="field-input"
                            @error('catatan_revisi') aria-invalid="true" aria-describedby="catatan_revisi-error" @enderror
                        >{{ old('catatan_revisi') }}</textarea>
                        <x-ui.field-error name="catatan_revisi" />
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Foto pendukung</h2>

                @if ($fotoSebelumnya->isNotEmpty())
                    <div class="mt-4">
                        <p class="text-sm font-medium text-ink-700">Foto pada versi sebelumnya (centang untuk tetap dilampirkan pada revisi baru)</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($fotoSebelumnya as $foto)
                                @php
                                    $terpilih = old('pertahankan_foto') !== null
                                        ? in_array((string) $foto->id, array_map('strval', (array) old('pertahankan_foto')), true)
                                        : true;
                                @endphp
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line bg-rimba-50/60 p-3">
                                    <input
                                        type="checkbox"
                                        name="pertahankan_foto[]"
                                        value="{{ $foto->id }}"
                                        @checked($terpilih)
                                        class="h-4 w-4 rounded border-line-strong text-rimba-600 focus:ring-rimba-600"
                                    >
                                    <img
                                        src="{{ route('masyarakat.laporan.media', [$laporan, $foto]) }}"
                                        alt="{{ $foto->nama_asli }}"
                                        class="h-12 w-12 shrink-0 rounded-lg object-cover"
                                    >
                                    <span class="min-w-0 truncate text-xs font-medium text-ink-700">{{ $foto->nama_asli }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <label for="foto" class="field-label">Unggah foto tambahan (JPG/PNG/WEBP, maks. 5 MB per berkas, total maks. 5 foto)</label>
                    <input
                        id="foto"
                        name="foto[]"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="field-input"
                        @error('foto') aria-invalid="true" aria-describedby="foto-error" @enderror
                    >
                    <x-ui.field-error name="foto" />
                    @error('foto.*')
                        <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('masyarakat.laporan.show', $laporan) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Kirim ulang laporan</button>
            </div>
        </form>
    </x-ui.card>
@endsection
