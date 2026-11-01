<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Sumber Data</title>
</head>
<body>
    <h1>Katalog Sumber Data</h1>

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

    <p><a href="{{ route('katalog.sumber-data.create') }}">Tambah sumber data</a></p>

    <form method="GET" action="{{ route('katalog.sumber-data.index') }}">
        <p>
            <label for="cari">Cari nama</label>
            <input id="cari" name="cari" type="search" value="{{ request('cari') }}">
        </p>
        <p>
            <label for="jenis_sumber">Jenis</label>
            <select id="jenis_sumber" name="jenis_sumber">
                <option value="">Semua</option>
                <option value="INTERNAL" @selected(request('jenis_sumber') === 'INTERNAL')>Internal</option>
                <option value="EKSTERNAL" @selected(request('jenis_sumber') === 'EKSTERNAL')>Eksternal</option>
            </select>
        </p>
        <p>
            <label for="is_aktif">Status</label>
            <select id="is_aktif" name="is_aktif">
                <option value="">Semua</option>
                <option value="1" @selected(request('is_aktif') === '1')>Aktif</option>
                <option value="0" @selected(request('is_aktif') === '0')>Nonaktif</option>
            </select>
        </p>
        <button type="submit">Terapkan</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Penyedia</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sumberData as $sumber)
                <tr>
                    <td>
                        <a href="{{ route('katalog.sumber-data.show', $sumber) }}">{{ $sumber->nama_sumber }}</a>
                    </td>
                    <td>{{ $sumber->jenis_sumber }}</td>
                    <td>{{ $sumber->nama_penyedia }}</td>
                    <td>{{ $sumber->is_aktif ? 'Aktif' : 'Nonaktif' }}</td>
                    <td><a href="{{ route('katalog.sumber-data.edit', $sumber) }}">Ubah</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada sumber data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $sumberData->links() }}
</body>
</html>
