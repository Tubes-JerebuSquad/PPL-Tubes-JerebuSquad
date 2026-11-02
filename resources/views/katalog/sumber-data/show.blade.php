<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sumberData->nama_sumber }}</title>
</head>
<body>
    <p><a href="{{ route('katalog.sumber-data.index') }}">Kembali ke katalog</a></p>

    <h1>{{ $sumberData->nama_sumber }}</h1>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <p>Status: <strong>{{ $sumberData->is_aktif ? 'Aktif' : 'Nonaktif' }}</strong></p>

    <p>
        <a href="{{ route('katalog.sumber-data.edit', $sumberData) }}">Ubah metadata</a>
    </p>

    <h2>Aksi</h2>

    @if ($sumberData->is_aktif)
        <form method="POST" action="{{ route('katalog.sumber-data.nonaktifkan', $sumberData) }}">
            @csrf
            @method('PATCH')

            <p>
                <label for="catatan">Alasan penonaktifan</label>
                <input id="catatan" name="catatan" type="text" maxlength="500">
            </p>

            <button type="submit">Nonaktifkan</button>
        </form>
    @else
        <form method="POST" action="{{ route('katalog.sumber-data.aktifkan', $sumberData) }}">
            @csrf
            @method('PATCH')
            <button type="submit">Aktifkan</button>
        </form>
    @endif

    <h2>Metadata</h2>

    <dl>
        <dt>Jenis sumber</dt>
        <dd>{{ $sumberData->jenis_sumber ?? '-' }}</dd>

        <dt>Nama penyedia</dt>
        <dd>{{ $sumberData->nama_penyedia }}</dd>

        <dt>URL sumber</dt>
        <dd>{{ $sumberData->url_sumber ?? '-' }}</dd>

        <dt>Nama lisensi</dt>
        <dd>{{ $sumberData->nama_lisensi }}</dd>

        <dt>Format data</dt>
        <dd>{{ $sumberData->format_data }}</dd>

        <dt>Deskripsi cakupan</dt>
        <dd>{{ $sumberData->deskripsi_cakupan ?? '-' }}</dd>

        <dt>Periode</dt>
        <dd>
            {{ $sumberData->periode_mulai?->format('d-m-Y') ?? '-' }}
            s.d.
            {{ $sumberData->periode_selesai?->format('d-m-Y') ?? '-' }}
        </dd>

        <dt>Kode CRS</dt>
        <dd>{{ $sumberData->kode_crs }}</dd>

        <dt>Interval penyegaran</dt>
        <dd>{{ $sumberData->interval_penyegaran_jam !== null ? $sumberData->interval_penyegaran_jam.' jam' : '-' }}</dd>

        <dt>Status kualitas</dt>
        <dd>{{ $sumberData->status_kualitas?->label() ?? '-' }}</dd>

        <dt>Dibuat oleh</dt>
        <dd>{{ $sumberData->pembuat?->nama_lengkap ?? '-' }}</dd>

        <dt>Dibuat pada</dt>
        <dd>{{ $sumberData->dibuat_pada?->format('d-m-Y H:i') ?? '-' }}</dd>

        <dt>Metadata terakhir diperbarui</dt>
        <dd>{{ $sumberData->diperbarui_pada?->format('d-m-Y H:i') ?? '-' }}</dd>

        <dt>Data terakhir diambil dari sumber</dt>
        <dd>{{ $sumberData->terakhir_diambil_pada?->format('d-m-Y H:i') ?? 'Belum pernah diambil' }}</dd>

        <dt>Status kebaruan data</dt>
        <dd>{{ $sumberData->status_kebaruan === 'UNKNOWN' ? 'Belum diketahui' : $sumberData->status_kebaruan }}</dd>
    </dl>

    <h2>Riwayat perubahan</h2>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Aksi</th>
                <th>Oleh</th>
                <th>Perubahan</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sumberData->riwayat as $riwayat)
                <tr>
                    <td>{{ $riwayat->dibuat_pada?->format('d-m-Y H:i') }}</td>
                    <td>{{ $riwayat->aksi->label() }}</td>
                    <td>{{ $riwayat->pengguna?->nama_lengkap ?? '-' }}</td>
                    <td>
                        @if (empty($riwayat->perubahan))
                            -
                        @else
                            <ul>
                                @foreach ($riwayat->perubahan as $field => $nilai)
                                    <li>
                                        {{ $field }}:
                                        {{ is_array($nilai) ? ($nilai['lama'] ?? '-') : $nilai }}
                                        &rarr;
                                        {{ is_array($nilai) ? ($nilai['baru'] ?? '-') : $nilai }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>{{ $riwayat->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada riwayat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
