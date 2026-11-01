<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Sumber Data</title>
</head>
<body>
    <h1>Tambah Sumber Data</h1>

    <p><a href="{{ route('katalog.sumber-data.index') }}">Kembali ke katalog</a></p>

    @include('katalog.sumber-data._form', [
        'aksi' => route('katalog.sumber-data.store'),
        'metode' => 'POST',
    ])
</body>
</html>
