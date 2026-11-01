<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Sumber Data</title>
</head>
<body>
    <h1>Ubah Sumber Data</h1>

    <p><a href="{{ route('katalog.sumber-data.show', $sumberData) }}">Kembali ke detail</a></p>

    @include('katalog.sumber-data._form', [
        'aksi' => route('katalog.sumber-data.update', $sumberData),
        'metode' => 'PUT',
    ])
</body>
</html>
