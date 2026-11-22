@php
    $materiEdit = $materi ?? null;
    $opsiKategori = collect([
        'Pelestarian Hutan',
        'Pencegahan Kebakaran',
        'Rehabilitasi Lahan',
        'Edukasi Masyarakat',
    ])
        ->merge($daftarKategori ?? collect())
        ->unique()
        ->sort()
        ->values();
@endphp

<form method="POST" action="{{ $aksi }}" class="space-y-6">
    @csrf
    @if ($metode !== 'POST')
        @method($metode)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="judul" class="field-label">Judul materi <span class="text-danger-ink">*</span></label>
            <input
                id="judul"
                name="judul"
                type="text"
                maxlength="250"
                value="{{ old('judul', $materiEdit?->judul) }}"
                placeholder="Contoh: Panduan Pencegahan Kebakaran Lahan Gambut"
                class="field-input"
                required
                @error('judul') aria-invalid="true" aria-describedby="judul-error" @enderror
            >
            <x-ui.field-error name="judul" />
        </div>

        <div class="sm:col-span-2">
            <label for="kategori" class="field-label">Kategori <span class="text-danger-ink">*</span></label>
            <input
                id="kategori"
                name="kategori"
                type="text"
                maxlength="100"
                list="daftar-kategori-edukasi"
                value="{{ old('kategori', $materiEdit?->kategori) }}"
                placeholder="Pilih atau ketik kategori materi..."
                class="field-input"
                required
                @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror
            >
            <datalist id="daftar-kategori-edukasi">
                @foreach ($opsiKategori as $opsi)
                    <option value="{{ $opsi }}"></option>
                @endforeach
            </datalist>
            <x-ui.field-error name="kategori" />
        </div>

        <div class="sm:col-span-2">
            <label for="konten" class="field-label">Isi materi <span class="text-danger-ink">*</span></label>
            <textarea
                id="konten"
                name="konten"
                rows="10"
                maxlength="20000"
                placeholder="Tuliskan penjelasan lengkap mengenai pelestarian, pencegahan, atau pemulihan kawasan hijau..."
                class="field-input resize-y"
                required
                @error('konten') aria-invalid="true" aria-describedby="konten-error" @enderror
            >{{ old('konten', $materiEdit?->konten) }}</textarea>
            <x-ui.field-error name="konten" />
        </div>

        <div class="sm:col-span-2">
            <label for="referensi_sumber" class="field-label">Sumber rujukan <span class="text-danger-ink">*</span></label>
            <textarea
                id="referensi_sumber"
                name="referensi_sumber"
                rows="3"
                maxlength="2000"
                placeholder="Contoh: Kementerian Lingkungan Hidup dan Kehutanan (KLHK) · Pedoman Restorasi Ekosistem 2025"
                class="field-input resize-y"
                required
                @error('referensi_sumber') aria-invalid="true" aria-describedby="referensi_sumber-error" @enderror
            >{{ old('referensi_sumber', $materiEdit?->referensi_sumber) }}</textarea>
            <x-ui.field-error name="referensi_sumber" />
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-ink-400">
            Kolom bertanda <span class="text-danger-ink">*</span> wajib diisi. Materi baru tersimpan sebagai draf hingga diterbitkan.
        </p>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:shrink-0">
            <a href="{{ $batal }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </div>
</form>
