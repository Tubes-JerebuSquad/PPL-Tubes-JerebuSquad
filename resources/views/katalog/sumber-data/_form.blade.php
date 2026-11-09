@php($sumber = $sumberData ?? null)

<form method="POST" action="{{ $aksi }}" class="space-y-8">
    @csrf
    @if ($metode !== 'POST')
        @method($metode)
    @endif

    <section>
        <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Identitas dataset</h2>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="nama_sumber" class="field-label">Nama sumber <span class="text-danger-ink">*</span></label>
                <input id="nama_sumber" name="nama_sumber" type="text" maxlength="200"
                    value="{{ old('nama_sumber', $sumber?->nama_sumber) }}" class="field-input" required
                    @error('nama_sumber') aria-invalid="true" aria-describedby="nama_sumber-error" @enderror>
                <x-ui.field-error name="nama_sumber" />
            </div>

            <div>
                <label for="jenis_sumber" class="field-label">Jenis sumber <span class="text-danger-ink">*</span></label>
                <select id="jenis_sumber" name="jenis_sumber" class="field-input" required
                    @error('jenis_sumber') aria-invalid="true" aria-describedby="jenis_sumber-error" @enderror>
                    @foreach ([\App\Models\SumberData::JENIS_INTERNAL, \App\Models\SumberData::JENIS_EKSTERNAL] as $jenis)
                        <option value="{{ $jenis }}" @selected(old('jenis_sumber', $sumber?->jenis_sumber) === $jenis)>
                            {{ $jenis }}
                        </option>
                    @endforeach
                </select>
                <x-ui.field-error name="jenis_sumber" />
            </div>

            <div class="sm:col-span-2">
                <label for="nama_penyedia" class="field-label">Nama penyedia <span class="text-danger-ink">*</span></label>
                <input id="nama_penyedia" name="nama_penyedia" type="text" maxlength="200"
                    value="{{ old('nama_penyedia', $sumber?->nama_penyedia) }}" class="field-input" required
                    @error('nama_penyedia') aria-invalid="true" aria-describedby="nama_penyedia-error" @enderror>
                <x-ui.field-error name="nama_penyedia" />
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Sumber &amp; lisensi</h2>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="url_sumber" class="field-label">URL sumber</label>
                <input id="url_sumber" name="url_sumber" type="url" maxlength="2048"
                    value="{{ old('url_sumber', $sumber?->url_sumber) }}" placeholder="https://..." class="field-input"
                    @error('url_sumber') aria-invalid="true" aria-describedby="url_sumber-error" @enderror>
                <x-ui.field-error name="url_sumber" />
            </div>

            <div>
                <label for="nama_lisensi" class="field-label">Nama lisensi <span class="text-danger-ink">*</span></label>
                <input id="nama_lisensi" name="nama_lisensi" type="text" maxlength="200"
                    value="{{ old('nama_lisensi', $sumber?->nama_lisensi) }}" class="field-input" required
                    @error('nama_lisensi') aria-invalid="true" aria-describedby="nama_lisensi-error" @enderror>
                <x-ui.field-error name="nama_lisensi" />
            </div>

            <div>
                <label for="format_data" class="field-label">Format data <span class="text-danger-ink">*</span></label>
                <input id="format_data" name="format_data" type="text" maxlength="50"
                    value="{{ old('format_data', $sumber?->format_data) }}" placeholder="GeoJSON, CSV, ..." class="field-input" required
                    @error('format_data') aria-invalid="true" aria-describedby="format_data-error" @enderror>
                <x-ui.field-error name="format_data" />
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Cakupan &amp; periode</h2>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="deskripsi_cakupan" class="field-label">Deskripsi cakupan</label>
                <textarea id="deskripsi_cakupan" name="deskripsi_cakupan" maxlength="2000" rows="3"
                    class="field-input" placeholder="Isi metadata, cakupan wilayah, dan periode data..."
                    @error('deskripsi_cakupan') aria-invalid="true" aria-describedby="deskripsi_cakupan-error" @enderror>{{ old('deskripsi_cakupan', $sumber?->deskripsi_cakupan) }}</textarea>
                <x-ui.field-error name="deskripsi_cakupan" />
            </div>

            <div>
                <label for="periode_mulai" class="field-label">Periode mulai</label>
                <input id="periode_mulai" name="periode_mulai" type="date"
                    value="{{ old('periode_mulai', $sumber?->periode_mulai?->format('Y-m-d')) }}" class="field-input"
                    @error('periode_mulai') aria-invalid="true" aria-describedby="periode_mulai-error" @enderror>
                <x-ui.field-error name="periode_mulai" />
            </div>

            <div>
                <label for="periode_selesai" class="field-label">Periode selesai</label>
                <input id="periode_selesai" name="periode_selesai" type="date"
                    value="{{ old('periode_selesai', $sumber?->periode_selesai?->format('Y-m-d')) }}" class="field-input"
                    @error('periode_selesai') aria-invalid="true" aria-describedby="periode_selesai-error" @enderror>
                <x-ui.field-error name="periode_selesai" />
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400">Teknis &amp; kualitas</h2>

        <div class="mt-4 grid gap-5 sm:grid-cols-3">
            <div>
                <label for="kode_crs" class="field-label">Kode CRS <span class="text-danger-ink">*</span></label>
                <input id="kode_crs" name="kode_crs" type="text" maxlength="50"
                    value="{{ old('kode_crs', $sumber?->kode_crs ?? 'EPSG:4326') }}" class="field-input" required
                    @error('kode_crs') aria-invalid="true" aria-describedby="kode_crs-error" @enderror>
                <x-ui.field-error name="kode_crs" />
            </div>

            <div>
                <label for="interval_penyegaran_jam" class="field-label">Interval penyegaran (jam)</label>
                <input id="interval_penyegaran_jam" name="interval_penyegaran_jam" type="number" min="1" max="8760"
                    value="{{ old('interval_penyegaran_jam', $sumber?->interval_penyegaran_jam) }}" class="field-input"
                    @error('interval_penyegaran_jam') aria-invalid="true" aria-describedby="interval_penyegaran_jam-error" @enderror>
                <x-ui.field-error name="interval_penyegaran_jam" />
            </div>

            <div>
                <label for="status_kualitas" class="field-label">Status kualitas <span class="text-danger-ink">*</span></label>
                <select id="status_kualitas" name="status_kualitas" class="field-input" required
                    @error('status_kualitas') aria-invalid="true" aria-describedby="status_kualitas-error" @enderror>
                    @foreach (\App\Enums\StatusKualitas::pilihan() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('status_kualitas', $sumber?->status_kualitas?->value) === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <x-ui.field-error name="status_kualitas" />
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
        <a href="{{ $batal }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
