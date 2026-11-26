@php
    $kegiatan ??= null;
    $sedangDiubah = $kegiatan !== null;
    $sudahDiterbitkan = $sedangDiubah && $kegiatan->status_kegiatan === \App\Enums\StatusKegiatan::DITERBITKAN;

    $kelasLabel = 'mb-1.5 block text-sm font-medium text-ink-700';
    $kelasDasar = 'field-input block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-ink-900 outline-none transition placeholder:text-ink-400 focus:ring-2';
    $kelasNormal = 'border-line-strong focus:border-rimba-600 focus:ring-rimba-600/20';
    $kelasGalat = 'border-danger-ink focus:border-danger-ink focus:ring-danger-ink/20';
    $kelasInput = fn (string $nama) => $kelasDasar.' '.($errors->has($nama) ? $kelasGalat : $kelasNormal);
    $nilai = fn (string $nama) => old($nama, $kegiatan?->{$nama});
@endphp

<form method="POST"
      action="{{ $sedangDiubah ? route('pengawas.kegiatan.update', $kegiatan) : route('pengawas.kegiatan.store') }}"
      class="grid gap-4 sm:grid-cols-2">
    @csrf
    @if ($sedangDiubah)
        @method('PUT')
    @endif

    <div class="sm:col-span-2">
        <label for="nama_kegiatan" class="{{ $kelasLabel }}">Nama kegiatan</label>
        <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="{{ $nilai('nama_kegiatan') }}"
               required maxlength="200" class="{{ $kelasInput('nama_kegiatan') }}">
        @error('nama_kegiatan')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kawasan_id" class="{{ $kelasLabel }}">Kawasan</label>
        <select id="kawasan_id" name="kawasan_id" required class="{{ $kelasInput('kawasan_id') }}">
            <option value="">Pilih kawasan</option>
            @foreach ($daftarKawasan as $kawasan)
                <option value="{{ $kawasan->kawasan_id }}"
                        @selected((int) $nilai('kawasan_id') === (int) $kawasan->kawasan_id)>
                    {{ $kawasan->nama_kawasan }}
                </option>
            @endforeach
        </select>
        @error('kawasan_id')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis_kegiatan" class="{{ $kelasLabel }}">Jenis kegiatan</label>
        <input type="text" id="jenis_kegiatan" name="jenis_kegiatan" value="{{ $nilai('jenis_kegiatan') }}"
               list="saran-jenis-kegiatan" required maxlength="100" class="{{ $kelasInput('jenis_kegiatan') }}">
        <datalist id="saran-jenis-kegiatan">
            @foreach ($jenisSaran as $saran)
                <option value="{{ $saran }}"></option>
            @endforeach
        </datalist>
        @error('jenis_kegiatan')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_mulai" class="{{ $kelasLabel }}">Tanggal mulai</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai"
               value="{{ old('tanggal_mulai', $kegiatan?->tanggal_mulai?->format('Y-m-d')) }}"
               required class="{{ $kelasInput('tanggal_mulai') }}">
        @error('tanggal_mulai')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_selesai" class="{{ $kelasLabel }}">Tanggal selesai</label>
        <input type="date" id="tanggal_selesai" name="tanggal_selesai"
               value="{{ old('tanggal_selesai', $kegiatan?->tanggal_selesai?->format('Y-m-d')) }}"
               required class="{{ $kelasInput('tanggal_selesai') }}">
        @error('tanggal_selesai')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="tujuan" class="{{ $kelasLabel }}">Tujuan</label>
        <textarea id="tujuan" name="tujuan" rows="3" required class="{{ $kelasInput('tujuan') }}">{{ $nilai('tujuan') }}</textarea>
        @error('tujuan')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="deskripsi_target" class="{{ $kelasLabel }}">Target <span class="text-xs font-normal text-ink-400">(wajib sebelum diterbitkan)</span></label>
        <textarea id="deskripsi_target" name="deskripsi_target" rows="3" class="{{ $kelasInput('deskripsi_target') }}">{{ $nilai('deskripsi_target') }}</textarea>
        @error('deskripsi_target')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="penanggung_jawab" class="{{ $kelasLabel }}">Penanggung jawab <span class="text-xs font-normal text-ink-400">(wajib sebelum diterbitkan)</span></label>
        <input type="text" id="penanggung_jawab" name="penanggung_jawab" value="{{ $nilai('penanggung_jawab') }}"
               maxlength="200" class="{{ $kelasInput('penanggung_jawab') }}">
        @error('penanggung_jawab')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kuota_peserta" class="{{ $kelasLabel }}">Kuota peserta <span class="text-xs font-normal text-ink-400">(opsional)</span></label>
        <input type="number" id="kuota_peserta" name="kuota_peserta" value="{{ $nilai('kuota_peserta') }}"
               min="1" class="{{ $kelasInput('kuota_peserta') }}">
        @error('kuota_peserta')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="deskripsi_sumber_daya" class="{{ $kelasLabel }}">Kebutuhan sumber daya <span class="text-xs font-normal text-ink-400">(opsional)</span></label>
        <textarea id="deskripsi_sumber_daya" name="deskripsi_sumber_daya" rows="3" class="{{ $kelasInput('deskripsi_sumber_daya') }}">{{ $nilai('deskripsi_sumber_daya') }}</textarea>
        @error('deskripsi_sumber_daya')
            <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
        @enderror
    </div>

    @error('aksi')
        <p class="text-xs font-medium text-danger-ink sm:col-span-2">{{ $message }}</p>
    @enderror

    <div class="flex flex-wrap gap-2 sm:col-span-2">
        @if ($sudahDiterbitkan)
            <button type="submit" name="aksi" value="terbitkan" class="btn-primary">Simpan Perubahan</button>
        @else
            <button type="submit" name="aksi" value="draf" class="btn-secondary">Simpan sebagai Draf</button>
            <button type="submit" name="aksi" value="terbitkan" class="btn-primary">Terbitkan</button>
        @endif
        <a href="{{ $sedangDiubah ? route('pengawas.kegiatan.show', $kegiatan) : route('pengawas.kegiatan.index') }}"
           class="btn-secondary">Batal</a>
    </div>
</form>