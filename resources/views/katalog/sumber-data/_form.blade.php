@php($sumber = $sumberData ?? null)

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ $aksi }}">
    @csrf
    @if ($metode !== 'POST')
        @method($metode)
    @endif

    <p>
        <label for="nama_sumber">Nama sumber</label>
        <input id="nama_sumber" name="nama_sumber" type="text" maxlength="200"
            value="{{ old('nama_sumber', $sumber?->nama_sumber) }}" required>
    </p>

    <p>
        <label for="jenis_sumber">Jenis sumber</label>
        <select id="jenis_sumber" name="jenis_sumber" required>
            @foreach ([\App\Models\SumberData::JENIS_INTERNAL, \App\Models\SumberData::JENIS_EKSTERNAL] as $jenis)
                <option value="{{ $jenis }}" @selected(old('jenis_sumber', $sumber?->jenis_sumber) === $jenis)>
                    {{ $jenis }}
                </option>
            @endforeach
        </select>
    </p>

    <p>
        <label for="nama_penyedia">Nama penyedia</label>
        <input id="nama_penyedia" name="nama_penyedia" type="text" maxlength="200"
            value="{{ old('nama_penyedia', $sumber?->nama_penyedia) }}" required>
    </p>

    <p>
        <label for="url_sumber">URL sumber</label>
        <input id="url_sumber" name="url_sumber" type="url" maxlength="2048"
            value="{{ old('url_sumber', $sumber?->url_sumber) }}">
    </p>

    <p>
        <label for="nama_lisensi">Nama lisensi</label>
        <input id="nama_lisensi" name="nama_lisensi" type="text" maxlength="200"
            value="{{ old('nama_lisensi', $sumber?->nama_lisensi) }}" required>
    </p>

    <p>
        <label for="format_data">Format data</label>
        <input id="format_data" name="format_data" type="text" maxlength="50"
            value="{{ old('format_data', $sumber?->format_data) }}" required>
    </p>

    <p>
        <label for="deskripsi_cakupan">Deskripsi cakupan</label>
        <textarea id="deskripsi_cakupan" name="deskripsi_cakupan" maxlength="2000">{{ old('deskripsi_cakupan', $sumber?->deskripsi_cakupan) }}</textarea>
    </p>

    <p>
        <label for="periode_mulai">Periode mulai</label>
        <input id="periode_mulai" name="periode_mulai" type="date"
            value="{{ old('periode_mulai', $sumber?->periode_mulai?->format('Y-m-d')) }}">
    </p>

    <p>
        <label for="periode_selesai">Periode selesai</label>
        <input id="periode_selesai" name="periode_selesai" type="date"
            value="{{ old('periode_selesai', $sumber?->periode_selesai?->format('Y-m-d')) }}">
    </p>

    <p>
        <label for="kode_crs">Kode CRS</label>
        <input id="kode_crs" name="kode_crs" type="text" maxlength="50"
            value="{{ old('kode_crs', $sumber?->kode_crs ?? 'EPSG:4326') }}" required>
    </p>

    <p>
        <label for="interval_penyegaran_jam">Interval penyegaran (jam)</label>
        <input id="interval_penyegaran_jam" name="interval_penyegaran_jam" type="number" min="1" max="8760"
            value="{{ old('interval_penyegaran_jam', $sumber?->interval_penyegaran_jam) }}">
    </p>

    <p>
        <label for="status_kualitas">Status kualitas</label>
        <select id="status_kualitas" name="status_kualitas" required>
            @foreach (\App\Enums\StatusKualitas::pilihan() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(old('status_kualitas', $sumber?->status_kualitas?->value) === $nilai)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </p>

    <button type="submit">Simpan</button>
</form>
