@php
    $kawasan ??= null;
    $sedangDiubah = $kawasan !== null;

    // Kelas utilitas disertakan langsung agar formulir tetap tampil benar
    // saat aplikasi memakai fallback Tailwind CDN (Vite belum di-build).
    $kelasLabel = 'mb-1.5 block text-sm font-medium text-ink-700';
    $kelasDasarInput = 'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-ink-900 outline-none transition placeholder:text-ink-400 focus:ring-2';
    $kelasInputNormal = 'border-line-strong focus:border-rimba-600 focus:ring-rimba-600/20';
    $kelasInputGalat = 'border-danger-ink focus:border-danger-ink focus:ring-danger-ink/20';
@endphp

<form method="POST"
      action="{{ $sedangDiubah ? route('admin.kawasan.update', $kawasan) : route('admin.kawasan.store') }}"
      class="grid gap-4 sm:grid-cols-2">
    @csrf
    @if ($sedangDiubah)
        @method('PUT')
    @endif

    <div class="sm:col-span-2">
        <label for="nama_kawasan" class="{{ $kelasLabel }}">Nama kawasan</label>
        <input type="text" id="nama_kawasan" name="nama_kawasan"
               value="{{ old('nama_kawasan', $kawasan?->nama_kawasan) }}"
               required maxlength="200"
               @class([
                   'field-input',
                   $kelasDasarInput,
                   $errors->has('nama_kawasan') ? $kelasInputGalat : $kelasInputNormal,
               ])
               @error('nama_kawasan') aria-invalid="true" aria-describedby="nama_kawasan-error" @enderror>
        @error('nama_kawasan')
            <p id="nama_kawasan-error" class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="wilayah_id" class="{{ $kelasLabel }}">Wilayah</label>
        <select id="wilayah_id" name="wilayah_id" required
                @class([
                    'field-input',
                    $kelasDasarInput,
                    $errors->has('wilayah_id') ? $kelasInputGalat : $kelasInputNormal,
                ])
                @error('wilayah_id') aria-invalid="true" aria-describedby="wilayah_id-error" @enderror>
            <option value="">Pilih wilayah</option>
            @foreach ($daftarWilayah as $wilayah)
                <option value="{{ $wilayah->wilayah_id }}"
                        @selected((int) old('wilayah_id', $kawasan?->wilayah_id) === (int) $wilayah->wilayah_id)>
                    {{ $wilayah->nama_wilayah }}{{ $wilayah->is_aktif ? '' : ' (nonaktif)' }}
                </option>
            @endforeach
        </select>
        @if ($daftarWilayah->isEmpty())
            <p class="mt-1.5 text-xs text-ink-400">Belum ada wilayah aktif yang dapat dipilih.</p>
        @endif
        @error('wilayah_id')
            <p id="wilayah_id-error" class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis_kawasan" class="{{ $kelasLabel }}">Jenis kawasan</label>
        <input type="text" id="jenis_kawasan" name="jenis_kawasan"
               value="{{ old('jenis_kawasan', $kawasan?->jenis_kawasan) }}"
               required maxlength="100" placeholder="Contoh: HUTAN_LINDUNG"
               @class([
                   'field-input',
                   $kelasDasarInput,
                   $errors->has('jenis_kawasan') ? $kelasInputGalat : $kelasInputNormal,
               ])
               @error('jenis_kawasan') aria-invalid="true" aria-describedby="jenis_kawasan-error" @enderror>
        @error('jenis_kawasan')
            <p id="jenis_kawasan-error" class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status_kawasan" class="{{ $kelasLabel }}">Status Kawasan</label>
        <input type="text" id="status_kawasan" name="status_kawasan"
               value="{{ old('status_kawasan', $kawasan?->status_kawasan) }}"
               required maxlength="50" placeholder="Masukkan status kawasan"
               @class([
                   'field-input',
                   $kelasDasarInput,
                   $errors->has('status_kawasan') ? $kelasInputGalat : $kelasInputNormal,
               ])
               @error('status_kawasan') aria-invalid="true" aria-describedby="status_kawasan-error" @enderror>
        @error('status_kawasan')
            <p id="status_kawasan-error" class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="catatan" class="{{ $kelasLabel }}">Catatan (opsional)</label>
        <textarea id="catatan" name="catatan" rows="3" maxlength="2000"
                  @class([
                      'field-input',
                      $kelasDasarInput,
                      $errors->has('catatan') ? $kelasInputGalat : $kelasInputNormal,
                      'resize-y',
                  ])
                  @error('catatan') aria-invalid="true" aria-describedby="catatan-error" @enderror>{{ old('catatan', $kawasan?->catatan) }}</textarea>
        @error('catatan')
            <p id="catatan-error" class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <input type="hidden" name="is_aktif" value="0">
        <label for="is_aktif" class="inline-flex items-center gap-2 text-sm font-medium text-ink-700">
            <input type="checkbox" id="is_aktif" name="is_aktif" value="1"
                   @checked(old('is_aktif', $kawasan?->is_aktif ?? true))
                   class="h-4 w-4 shrink-0 rounded border-line-strong accent-rimba-600 focus:ring-2 focus:ring-rimba-600/30">
            Aktifkan Data Kawasan
        </label>
        <p class="mt-1.5 text-xs text-ink-400">Data nonaktif tetap tersimpan, tetapi ditandai tidak aktif pada daftar.</p>
    </div>

    <div class="flex flex-wrap gap-2 sm:col-span-2">
        <button type="submit" class="btn-primary">{{ $sedangDiubah ? 'Simpan perubahan' : 'Simpan' }}</button>
        <a href="{{ route('admin.kawasan.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
