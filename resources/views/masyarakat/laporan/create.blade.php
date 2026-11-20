
@extends('layouts.app')

@section('title', 'Ajukan Laporan - RimbaWatch')
@section('breadcrumb', 'Ajukan Laporan')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="rounded-2xl border border-line bg-white p-6 shadow-sm">
        <h1 class="text-xl font-bold text-gray-900">Ajukan Laporan Kerusakan Hutan</h1>
        <p class="mt-1 text-sm text-gray-600">
            Isi data kejadian selengkap mungkin. Foto dan lokasi wajib, video boleh dilewati.
        </p>

        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Laporan belum bisa dikirim:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ route('masyarakat.laporan.store') }}"
            enctype="multipart/form-data"
            class="mt-6 space-y-5">

            @csrf

            {{-- Wilayah --}}
            <div>
                <label for="wilayah_id" class="block text-sm font-semibold text-gray-800">
                    Wilayah
                </label>

                <select id="wilayah_id" name="wilayah_id" required
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="">Pilih wilayah</option>

                    @foreach ($wilayah as $item)
                        <option value="{{ $item->wilayah_id }}"
                            @selected(old('wilayah_id') == $item->wilayah_id)>
                            {{ $item->nama_wilayah }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kategori --}}
            <div>
                <label for="kategori" class="block text-sm font-semibold text-gray-800">
                    Kategori
                </label>

                <select id="kategori" name="kategori" required
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="">Pilih kategori</option>

                    @foreach ($kategori as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('kategori') === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="deskripsi" class="block text-sm font-semibold text-gray-800">
                    Deskripsi kejadian
                </label>

                <textarea id="deskripsi" name="deskripsi" rows="5" required
                    minlength="20" maxlength="2000"
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm"
                    placeholder="Ceritakan apa yang kamu lihat, kira-kira luasnya, dan patokan lokasinya.">{{ old('deskripsi') }}</textarea>
            </div>

            {{-- Waktu kejadian --}}
            <div>
                <label for="insiden_pada" class="block text-sm font-semibold text-gray-800">
                    Waktu kejadian
                </label>

                <input id="insiden_pada" name="insiden_pada" type="datetime-local"
                    required value="{{ old('insiden_pada') }}"
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </div>

            {{-- Lokasi --}}
            <fieldset class="rounded-xl border border-line p-4">
                <legend class="px-1 text-sm font-semibold text-gray-800">
                    Lokasi
                </legend>

                <button type="button" id="btn-lokasi"
                    class="rounded-lg bg-rimba-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rimba-700">
                    Ambil lokasi saya
                </button>

                <p id="info-lokasi" class="mt-2 text-xs text-gray-600" aria-live="polite">
                    Izinkan akses lokasi di browser. Kalau gagal, isi koordinat manual di bawah.
                </p>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-gray-700">
                            Latitude
                        </label>

                        <input id="latitude" name="latitude" type="number"
                            step="any" min="-90" max="90" required
                            value="{{ old('latitude') }}"
                            class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-gray-700">
                            Longitude
                        </label>

                        <input id="longitude" name="longitude" type="number"
                            step="any" min="-180" max="180" required
                            value="{{ old('longitude') }}"
                            class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                    </div>
                </div>
            </fieldset>

            {{-- Foto --}}
            <div>
                <label for="foto" class="block text-sm font-semibold text-gray-800">
                    Foto (wajib, 1-5 file, JPG/PNG, maks 5 MB)
                </label>

                <input id="foto" name="foto[]" type="file"
                    accept="image/jpeg,image/png" multiple required
                    class="mt-1 w-full text-sm">
            </div>

            {{-- Video --}}
            <div>
                <label for="video" class="block text-sm font-semibold text-gray-800">
                    Video (opsional, MP4/MOV, maks 50 MB)
                </label>

                <input id="video" name="video" type="file"
                    accept="video/mp4,video/quicktime"
                    class="mt-1 w-full text-sm">
            </div>

            {{-- Tombol --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('masyarakat.laporan.create') }}"
                    class="text-sm font-medium text-gray-600 hover:underline">
                    Batal
                </a>

                <button type="submit"
                    class="rounded-lg bg-rimba-600 px-5 py-2 text-sm font-semibold text-white hover:bg-rimba-700">
                    Kirim laporan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var tombol = document.getElementById('btn-lokasi');
        var info = document.getElementById('info-lokasi');
        var lat = document.getElementById('latitude');
        var lng = document.getElementById('longitude');

        tombol.addEventListener('click', function () {
            if (!navigator.geolocation) {
                info.textContent = 'Browser ini tidak mendukung GPS. Isi koordinat manual.';
                return;
            }

            info.textContent = 'Mengambil lokasi...';

            navigator.geolocation.getCurrentPosition(function (pos) {
                lat.value = pos.coords.latitude.toFixed(7);
                lng.value = pos.coords.longitude.toFixed(7);

                info.textContent = 'Lokasi terisi (akurasi sekitar ' +
                    Math.round(pos.coords.accuracy) + ' m).';
            }, function () {
                info.textContent = 'Lokasi gagal diambil (izin ditolak atau sinyal lemah). Isi koordinat manual.';
            }, {
                enableHighAccuracy: true,
                timeout: 15000
            });
        });
    })();
</script>
@endsection
