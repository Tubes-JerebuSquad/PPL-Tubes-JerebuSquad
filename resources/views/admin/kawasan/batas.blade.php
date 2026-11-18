@extends('layouts.app')

@section('title', 'Digitasi Batas Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan / Digitasi Batas')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .vertex-handle {
            width: 12px;
            height: 12px;
            background-color: #225a38;
            border: 2.5px solid #FFFFFF;
            border-radius: 9999px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.28);
            cursor: grab;
        }
        .vertex-handle:active {
            cursor: grabbing;
            transform: scale(1.15);
        }
        .leaflet-container {
            z-index: 10;
        }
        .leaflet-tile-pane {
            filter: saturate(0.78) brightness(1.02);
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div x-data="batasKawasanEditor({
        initialGeojson: @js($batasAktif?->geojson),
        hasSavedBoundary: @js((bool) $batasAktif)
    })">
        <!-- Page Header -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl font-semibold text-ink-900">Digitasi Batas Kawasan</h1>
                    <x-ui.badge variant="info">
                        Versi Aktif: {{ $batasAktif ? 'v' . $batasAktif->nomor_versi : 'Belum Ada' }}
                    </x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-ink-500">
                    Gambar dan validasi polygon kawasan secara interaktif atau unggah berkas GeoJSON terstandar.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.kawasan.show', $kawasan->kawasan_id) }}" class="btn-secondary">
                    &larr; Kembali ke detail
                </a>
            </div>
        </div>

        <!-- Feedback Validasi / Sukses dari Server -->
        @if (session('success'))
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-rimba-600/20 bg-rimba-100 px-4 py-3 text-sm font-medium text-rimba-700" role="status">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.3a1 1 0 0 1-1.42 0l-3.25-3.27a1 1 0 1 1 1.42-1.41l2.54 2.55 6.54-6.59a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-danger-ink/20 bg-danger-soft px-4 py-3 text-sm text-danger-ink" role="alert">
                <span class="font-semibold">Validasi Ditolak:</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 2-COLUMN MAIN GRID -->
        <div class="mt-5 grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

            <!-- KOLOM KIRI (7/12): EDITOR POLYGON & IMPOR GEOJSON -->
            <x-ui.card class="p-6 lg:col-span-7">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-bold text-ink-900">Editor polygon</h2>

                        <!-- Mode Switcher (Digitasi vs Impor GeoJSON) -->
                        <div class="inline-flex rounded-lg border border-line bg-rimba-50 p-0.5 text-xs font-medium">
                            <button type="button"
                                    @click="mode = 'digitasi'"
                                    :class="mode === 'digitasi' ? 'bg-white text-ink-900 shadow-xs font-semibold' : 'text-ink-500 hover:text-ink-900'"
                                    class="rounded-md px-2.5 py-1 transition">
                                Digitasi Peta
                            </button>
                            <button type="button"
                                    @click="mode = 'impor'"
                                    :class="mode === 'impor' ? 'bg-white text-ink-900 shadow-xs font-semibold' : 'text-ink-500 hover:text-ink-900'"
                                    class="rounded-md px-2.5 py-1 transition">
                                Impor GeoJSON
                            </button>
                        </div>
                    </div>

                    <x-ui.badge variant="info">WGS84 • SRID 4326</x-ui.badge>
                </div>

                <!-- MODE 1: PETA DIGITASI INTERAKTIF -->
                <div x-show="mode === 'digitasi'">
                    <div class="relative h-[400px] w-full overflow-hidden rounded-xl border border-line-strong bg-rimba-50">
                        <div id="map-digitasi" class="h-full w-full"></div>

                        <!-- Floating Toolbar Kiri Atas -->
                        <div class="absolute left-3.5 top-3.5 z-[500] inline-flex items-center divide-x divide-line rounded-xl border border-line-strong bg-white/95 text-xs font-semibold text-ink-900 shadow-md backdrop-blur-xs">
                            <button type="button"
                                    @click="toggleDrawMode()"
                                    :class="isDrawing ? 'bg-rimba-100 text-rimba-700' : 'hover:bg-rimba-50'"
                                    class="flex items-center gap-1.5 rounded-l-xl px-3.5 py-2 transition">
                                <span class="text-sm font-bold leading-none">+</span>
                                <span x-text="isDrawing ? 'Selesai klik peta' : 'Tambah titik'"></span>
                            </button>
                            <button type="button"
                                    @click="undoPoint()"
                                    :disabled="points.length === 0"
                                    class="px-3.5 py-2 transition hover:bg-rimba-50 disabled:opacity-40">
                                Undo
                            </button>
                            <button type="button"
                                    @click="resetPolygon()"
                                    :disabled="points.length === 0"
                                    class="rounded-r-xl px-3 py-2 text-danger-ink transition hover:bg-danger-soft disabled:opacity-40">
                                Reset
                            </button>
                        </div>

                        <!-- Floating Status Pill Kiri Bawah -->
                        <div class="absolute bottom-3.5 left-3.5 z-[500] rounded-full border border-line-strong bg-white/95 px-3.5 py-1.5 text-xs font-medium text-ink-700 shadow-sm backdrop-blur-xs">
                            Polygon utama <span class="mx-1 text-ink-400">•</span>
                            <span class="font-bold text-ink-900" x-text="points.length"></span> titik batas aktif
                        </div>
                    </div>

                    <!-- Legenda -->
                    <div class="mt-3.5 flex flex-wrap items-center justify-between gap-2 text-xs text-ink-500">
                        <div class="flex items-center gap-5">
                            <span class="font-semibold text-ink-700">Legenda</span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-rimba-700"></span>
                                Batas kawasan
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#D6C79C]"></span>
                                Jalan / Referensi dasar
                            </span>
                        </div>
                        <span class="text-[11px] text-ink-400">Klik peta untuk tambah titik • Geser titik untuk ubah bentuk</span>
                    </div>

                    <!-- Action Buttons Footer Card Kiri -->
                    <div class="mt-5 grid grid-cols-2 gap-3.5 border-t border-line pt-4">
                        <button type="button"
                                @click="submitDigitasi('draf')"
                                class="btn-secondary w-full">
                            Simpan sebagai draf
                        </button>

                        <button type="button"
                                @click="validateGeometry()"
                                class="btn-primary w-full">
                            <span>Validasi polygon</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- MODE 2: IMPOR BERKAS GEOJSON -->
                <div x-show="mode === 'impor'" x-cloak>
                    <form action="{{ route('admin.kawasan.batas.impor', $kawasan->kawasan_id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-4">
                        @csrf
                        <input type="hidden" name="sumber_id" :value="selectedSumberId">

                        <div class="rounded-2xl border-2 border-dashed border-line-strong bg-rimba-50 p-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-rimba-100 text-rimba-700">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-ink-900">Unggah Berkas Spasial GeoJSON</h3>
                            <p class="mx-auto mt-1 max-w-md text-xs text-ink-500">
                                Mendukung format <code class="font-semibold text-rimba-700">.geojson</code> atau <code class="font-semibold text-rimba-700">.json</code> dengan tipe geometri <strong>Polygon</strong> atau <strong>MultiPolygon</strong> (WGS 84 / EPSG:4326, maks. 5 MB).
                            </p>

                            <div class="mt-5">
                                <input type="file"
                                       name="file_geojson"
                                       accept=".geojson,.json,application/geo+json,application/json"
                                       required
                                       class="mx-auto block w-full max-w-sm cursor-pointer text-xs text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-rimba-600 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-rimba-700">
                            </div>
                        </div>

                        <div class="rounded-xl border border-line bg-rimba-50 p-3.5 text-xs text-ink-700">
                            <strong>Jaminan Transaksi Atomik:</strong> Berkas yang gagal validasi format, CRS, atau topologi (<code class="text-rimba-700">ST_IsValid</code>) akan ditolak sepenuhnya tanpa menyisakan data parsial di database.
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="mode = 'digitasi'" class="btn-secondary">
                                Kembali ke Peta
                            </button>
                            <button type="submit" class="btn-primary">
                                Unggah &amp; Validasi GeoJSON
                            </button>
                        </div>
                    </form>
                </div>
            </x-ui.card>

            <!-- KOLOM KANAN (5/12): INFORMASI KAWASAN & PANEL VALIDASI -->
            <x-ui.card class="p-6 lg:col-span-5">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-base font-bold text-ink-900">Informasi kawasan</h2>
                    <x-ui.badge variant="success">
                        {{ $kawasan->kode_wilayah ?? 'AREA-' . str_pad($kawasan->kawasan_id, 2, '0', STR_PAD_LEFT) }}
                    </x-ui.badge>
                </div>

                <form id="form-digitasi" action="{{ route('admin.kawasan.batas.digitasi', $kawasan->kawasan_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="geojson_digitasi" x-model="geojsonString">
                    <input type="hidden" name="mode_simpan" x-model="modeSimpan">

                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Nama kawasan</label>
                            <div class="w-full rounded-lg border border-line bg-rimba-50 px-3.5 py-2.5 text-sm font-medium text-ink-900">
                                {{ $kawasan->nama_kawasan }}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Kategori</label>
                                <div class="w-full truncate rounded-lg border border-line bg-rimba-50 px-3.5 py-2.5 text-sm font-medium text-ink-900">
                                    {{ $kawasan->jenis_kawasan ?? 'Kawasan Konservasi' }}
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Wilayah pilot</label>
                                <div class="w-full truncate rounded-lg border border-line bg-rimba-50 px-3.5 py-2.5 text-sm font-medium text-ink-900">
                                    {{ $kawasan->nama_wilayah ?? 'Wilayah Utama' }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="sumber_id" class="field-label">Sumber batas</label>
                            <select id="sumber_id"
                                    name="sumber_id"
                                    x-model="selectedSumberId"
                                    class="field-input">
                                <option value="">Digitasi internal — data eksisting</option>
                                @foreach ($daftarSumber as $sumber)
                                    <option value="{{ $sumber->sumber_id }}" @selected($kawasan->sumber_id == $sumber->sumber_id)>
                                        {{ $sumber->nama_sumber }} ({{ $sumber->kode_crs }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3 KARTU METRIK SEJAJAR -->
                        <div class="grid grid-cols-3 gap-2.5 pt-1">
                            <div class="rounded-xl border border-line bg-rimba-50 p-3">
                                <div class="text-[11px] font-medium text-ink-400">Jumlah titik</div>
                                <div class="mt-1 text-xl font-extrabold text-ink-900" x-text="points.length"></div>
                            </div>

                            <div class="rounded-xl border border-line bg-rimba-50 p-3">
                                <div class="text-[11px] font-medium text-ink-400">Luas estimasi</div>
                                <div class="mt-1 text-xl font-extrabold text-ink-900">
                                    <span x-text="formattedArea"></span> <span class="text-sm font-bold">ha</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-line bg-rimba-50 p-3">
                                <div class="text-[11px] font-medium text-ink-400">Validasi</div>
                                <div class="mt-1 text-xl font-extrabold"
                                     :class="{
                                         'text-amber-ink': validationStatus === 'Belum',
                                         'text-rimba-700': validationStatus === 'Valid',
                                         'text-danger-ink': validationStatus === 'Gagal'
                                     }"
                                     x-text="validationStatus">
                                </div>
                            </div>
                        </div>

                        <!-- KOTAK STATUS VALIDASI DINAMIS -->
                        <div x-show="validationStatus === 'Belum'"
                             class="rounded-xl border border-amber-ink/20 bg-amber-soft p-3.5 text-amber-ink">
                            <div class="text-xs font-bold">Perlu validasi</div>
                            <p class="mt-0.5 text-[11.5px] leading-relaxed opacity-90">
                                Validasi geometri, batas wilayah, CRS, dan luas nyata perlu diperiksa sebelum disimpan.
                            </p>
                        </div>

                        <div x-show="validationStatus === 'Valid'" x-cloak
                             class="rounded-xl border border-rimba-600/20 bg-rimba-100 p-3.5 text-rimba-700">
                            <div class="text-xs font-bold">Geometri siap disimpan</div>
                            <p class="mt-0.5 text-[11.5px] leading-relaxed" x-text="validationMessage"></p>
                        </div>

                        <div x-show="validationStatus === 'Gagal'" x-cloak
                             class="rounded-xl border border-danger-ink/20 bg-danger-soft p-3.5 text-danger-ink">
                            <div class="text-xs font-bold">Geometri tidak valid</div>
                            <p class="mt-0.5 text-[11.5px] leading-relaxed" x-text="validationMessage"></p>
                        </div>

                        <!-- ATURAN VALIDASI -->
                        <div class="pt-1">
                            <div class="mb-2 text-xs font-bold text-ink-900">Aturan validasi</div>
                            <ul class="space-y-1.5 text-xs text-ink-500">
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-700"></span>
                                    <span>Minimal tiga titik, area tidak nol</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-700"></span>
                                    <span>Tidak ada polygon yang tumpang tindih sendiri</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rimba-700"></span>
                                    <span>Jangan menganggap batas demo legal</span>
                                </li>
                            </ul>
                        </div>

                        <!-- FOOTER TOMBOL KANAN -->
                        <div class="grid grid-cols-2 gap-3 border-t border-line pt-4">
                            <button type="button"
                                    @click="validateGeometry()"
                                    class="btn-primary w-full">
                                Validasi ulang
                            </button>

                            <button type="button"
                                    @click="submitDigitasi('final')"
                                    :class="validationStatus === 'Valid' ? 'btn-primary' : 'btn-secondary'"
                                    class="w-full">
                                Simpan batas
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- RIWAYAT VERSI BATAS KAWASAN -->
        <x-ui.card class="mt-6 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-6 py-4">
                <div>
                    <h3 class="text-base font-bold text-ink-900">Riwayat Versi Batas Kawasan</h3>
                    <p class="text-xs text-ink-500">Setiap perubahan digitasi atau impor GeoJSON disimpan sebagai versi baru tanpa menimpa riwayat lama.</p>
                </div>
                <x-ui.badge variant="success">Total {{ count($riwayatVersi) }} versi</x-ui.badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead class="bg-rimba-50">
                        <tr class="border-b border-line text-[11px] uppercase tracking-wider text-ink-400">
                            <th class="px-4 py-3 font-semibold">Versi</th>
                            <th class="px-4 py-3 font-semibold">CRS</th>
                            <th class="px-4 py-3 font-semibold">Luas Estimasi</th>
                            <th class="px-4 py-3 font-semibold">Status Validasi</th>
                            <th class="px-4 py-3 font-semibold">Catatan Validasi</th>
                            <th class="px-4 py-3 font-semibold">Dibuat Oleh</th>
                            <th class="px-4 py-3 font-semibold">Waktu Simpan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($riwayatVersi as $versi)
                            <tr class="hover:bg-rimba-50/50">
                                <td class="px-4 py-3 font-bold text-ink-900">v{{ $versi->nomor_versi }}</td>
                                <td class="px-4 py-3 text-ink-700">{{ $versi->kode_crs }}</td>
                                <td class="px-4 py-3 font-semibold text-ink-900">{{ number_format((float) $versi->luas_hektar, 2, ',', '.') }} ha</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge variant="success">{{ $versi->status_validasi }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-ink-700">{{ $versi->pesan_validasi }}</td>
                                <td class="px-4 py-3 text-ink-700">{{ $versi->pembuat ?? 'Admin Sistem' }}</td>
                                <td class="px-4 py-3 text-ink-400">{{ \Carbon\Carbon::parse($versi->dibuat_pada)->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-ink-400">
                                    Belum ada riwayat batas tersimpan untuk kawasan ini. Klik peta di atas untuk mulai digitasi atau impor file GeoJSON.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        if (!window.Alpine) {
            const alpineScript = document.createElement('script');
            alpineScript.defer = true;
            alpineScript.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
            document.head.appendChild(alpineScript);
        }

        function batasKawasanEditor({ initialGeojson, hasSavedBoundary }) {
            return {
                mode: 'digitasi',
                isDrawing: true,
                points: [],
                markers: [],
                polygonLayer: null,
                map: null,
                selectedSumberId: '{{ $kawasan->sumber_id ?? '' }}',
                validationStatus: hasSavedBoundary ? 'Valid' : 'Belum',
                validationMessage: hasSavedBoundary
                    ? 'Batas kawasan aktif telah tervalidasi dengan standar PostGIS WGS 84.'
                    : '',
                geojsonString: '',
                modeSimpan: 'final',

                init() {
                    const defaultCenter = [-0.5071, 101.4478];
                    this.map = L.map('map-digitasi', {
                        zoomControl: false
                    }).setView(defaultCenter, 14);

                    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    if (initialGeojson) {
                        try {
                            const parsed = JSON.parse(initialGeojson);
                            const coords = parsed.type === 'MultiPolygon'
                                ? parsed.coordinates[0][0]
                                : parsed.coordinates[0];

                            if (Array.isArray(coords) && coords.length > 3) {
                                const ring = coords.slice(0, -1);
                                this.points = ring.map(c => [c[1], c[0]]);
                                this.isDrawing = false;
                                this.renderPolygon(true);
                            }
                        } catch (e) {
                            console.error('Gagal mem-parsing GeoJSON awal:', e);
                        }
                    }

                    this.map.on('click', (e) => {
                        if (!this.isDrawing) return;
                        this.points.push([e.latlng.lat, e.latlng.lng]);
                        this.validationStatus = 'Belum';
                        this.renderPolygon(false);
                    });
                },

                toggleDrawMode() {
                    this.isDrawing = !this.isDrawing;
                },

                undoPoint() {
                    if (this.points.length === 0) return;
                    this.points.pop();
                    this.validationStatus = 'Belum';
                    this.renderPolygon(false);
                },

                resetPolygon() {
                    this.points = [];
                    this.isDrawing = true;
                    this.validationStatus = 'Belum';
                    this.geojsonString = '';
                    this.renderPolygon(false);
                },

                renderPolygon(fitBounds = false) {
                    if (this.polygonLayer) {
                        this.map.removeLayer(this.polygonLayer);
                        this.polygonLayer = null;
                    }
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];

                    const vertexIcon = L.divIcon({
                        className: 'vertex-handle',
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    });

                    this.points.forEach((latlng, idx) => {
                        const marker = L.marker(latlng, {
                            icon: vertexIcon,
                            draggable: true
                        }).addTo(this.map);

                        marker.on('drag', (e) => {
                            const pos = e.target.getLatLng();
                            this.points[idx] = [pos.lat, pos.lng];
                            this.validationStatus = 'Belum';
                            this.updatePolygonShapeOnly();
                        });

                        this.markers.push(marker);
                    });

                    this.updatePolygonShapeOnly();

                    if (fitBounds && this.polygonLayer) {
                        this.map.fitBounds(this.polygonLayer.getBounds(), { padding: [50, 50] });
                    }
                },

                updatePolygonShapeOnly() {
                    if (this.polygonLayer) {
                        this.map.removeLayer(this.polygonLayer);
                        this.polygonLayer = null;
                    }

                    if (this.points.length >= 3) {
                        this.polygonLayer = L.polygon(this.points, {
                            color: '#225a38',
                            weight: 2.5,
                            dashArray: '6, 6',
                            fillColor: '#3f8f63',
                            fillOpacity: 0.24
                        }).addTo(this.map);

                        this.syncGeojsonInput();
                    } else if (this.points.length === 2) {
                        this.polygonLayer = L.polyline(this.points, {
                            color: '#225a38',
                            weight: 2.5,
                            dashArray: '6, 6'
                        }).addTo(this.map);
                        this.geojsonString = '';
                    } else {
                        this.geojsonString = '';
                    }
                },

                syncGeojsonInput() {
                    if (this.points.length < 3) {
                        this.geojsonString = '';
                        return;
                    }
                    const ring = this.points.map(p => [Number(p[1].toFixed(7)), Number(p[0].toFixed(7))]);
                    ring.push(ring[0]);

                    const geojson = {
                        type: 'Polygon',
                        coordinates: [ring]
                    };
                    this.geojsonString = JSON.stringify(geojson);
                },

                get areaHectares() {
                    if (this.points.length < 3) return 0;
                    const R = 6378137;
                    let total = 0;
                    const len = this.points.length;

                    for (let i = 0; i < len; i++) {
                        const [lat1, lon1] = this.points[i];
                        const [lat2, lon2] = this.points[(i + 1) % len];
                        const radLat1 = lat1 * Math.PI / 180;
                        const radLat2 = lat2 * Math.PI / 180;
                        const dLon = (lon2 - lon1) * Math.PI / 180;
                        total += dLon * (2 + Math.sin(radLat1) + Math.sin(radLat2));
                    }

                    const areaSqMeters = Math.abs(total * R * R / 2);
                    return areaSqMeters / 10000;
                },

                get formattedArea() {
                    return this.areaHectares.toLocaleString('id-ID', {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 1
                    });
                },

                validateGeometry() {
                    if (this.points.length < 3) {
                        this.validationStatus = 'Gagal';
                        this.validationMessage = 'Minimal diperlukan 3 titik sudut untuk membentuk poligon batas kawasan.';
                        return false;
                    }

                    if (this.areaHectares <= 0.0001) {
                        this.validationStatus = 'Gagal';
                        this.validationMessage = 'Luas area poligon bernilai nol. Pastikan titik-titik tidak segaris.';
                        return false;
                    }

                    if (this.hasSelfIntersection(this.points)) {
                        this.validationStatus = 'Gagal';
                        this.validationMessage = 'Poligon terdeteksi tumpang tindih / menyilang sendiri (self-intersecting).';
                        return false;
                    }

                    this.syncGeojsonInput();
                    this.validationStatus = 'Valid';
                    this.validationMessage = `Geometri valid (${this.points.length} titik, estimasi ${this.formattedArea} ha, WGS 84 / EPSG:4326).`;
                    return true;
                },

                hasSelfIntersection(pts) {
                    const n = pts.length;
                    if (n < 4) return false;

                    const ccw = (a, b, c) => (c[0] - a[0]) * (b[1] - a[1]) > (b[0] - a[0]) * (c[1] - a[1]);
                    const intersect = (a, b, c, d) => ccw(a, c, d) !== ccw(b, c, d) && ccw(a, b, c) !== ccw(a, b, d);

                    for (let i = 0; i < n; i++) {
                        const a = pts[i];
                        const b = pts[(i + 1) % n];
                        for (let j = i + 2; j < n; j++) {
                            if (i === 0 && j === n - 1) continue;
                            const c = pts[j];
                            const d = pts[(j + 1) % n];
                            if (intersect(a, b, c, d)) return true;
                        }
                    }
                    return false;
                },

                submitDigitasi(mode) {
                    if (!this.validateGeometry()) return;
                    this.modeSimpan = mode;
                    this.$nextTick(() => {
                        document.getElementById('form-digitasi').submit();
                    });
                }
            };
        }
    </script>
@endpush