<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digitasi Batas Kawasan — RimbaWatch</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F4F6F3;
            color: #16231B;
        }
        h1, h2, h3, .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Kustomisasi handle titik vertex agar persis desain Figma */
        .vertex-handle {
            width: 12px;
            height: 12px;
            background-color: #235D3A;
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
            font-family: 'Inter', sans-serif;
            z-index: 10;
        }
        .leaflet-tile-pane {
            filter: saturate(0.75) brightness(1.03);
        }
    </style>
</head>
<body class="min-h-screen flex antialiased">

    <!-- SIDEBAR KIRI (RimbaWatch Forest Green) -->
    <aside class="w-64 bg-[#143726] text-white flex flex-col justify-between shrink-0 select-none">
        <div>
            <!-- Brand Header -->
            <div class="px-5 py-5 flex items-center gap-3 border-b border-white/10">
                <div class="w-9 h-9 rounded-xl bg-[#276749] flex items-center justify-center shadow-inner border border-emerald-400/20">
                    <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.5 3.5-7 7.5-7 11a7 7 0 0014 0c0-3.5-2.5-7.5-7-11z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v11m0-7l-3-3m3 5l3-3" />
                    </svg>
                </div>
                <div>
                    <div class="font-display font-bold text-[15px] leading-tight tracking-tight text-white">RimbaWatch</div>
                    <div class="text-[10px] font-semibold tracking-widest text-emerald-300/75 uppercase mt-0.5">GIS Conservation</div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="px-3 py-5">
                <div class="px-3 mb-2.5 text-[10px] font-bold tracking-widest text-emerald-200/50 uppercase">
                    Workspace
                </div>
                <nav class="space-y-1 text-[13px] font-medium">
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                        <span>Overview</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>Peta Interaktif</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Verifikasi Laporan</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Penugasan Inspeksi</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Hasil Inspeksi</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span>Konservasi</span>
                    </a>

                    <!-- MENU AKTIF: Data Kawasan -->
                    <a href="{{ route('admin.kawasan.batas.index', $kawasan->kawasan_id) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-[#275B40] text-white font-semibold shadow-sm border border-emerald-300/15">
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Data Kawasan</span>
                    </a>

                    <a href="{{ url('/admin/pengguna') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100/75 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Pengguna &amp; Akses</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 m-3 rounded-xl bg-black/15 border border-white/10">
            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-300/70">Modul Spasial PBI-02</div>
            <div class="text-xs text-emerald-100/80 mt-0.5">PostGIS • WGS 84 (4326)</div>
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA -->
    <div class="flex-1 flex flex-col min-w-0"
         x-data="batasKawasanEditor({
             initialGeojson: @js($batasAktif?->geojson),
             hasSavedBoundary: @js((bool) $batasAktif)
         })">

        <!-- TOP BAR -->
        <header class="h-14 bg-[#F4F6F3] border-b border-[#E3E8E2] px-8 flex items-center justify-between">
            <div class="text-xs text-[#64746B] font-medium">
                <span>RimbaWatch</span>
                <span class="mx-1.5 text-[#9AA89F]">/</span>
                <span class="text-[#16231B] font-semibold">Digitasi Batas Kawasan</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#E4EFE7] text-[#235D3A] border border-[#CBE0D2]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#235D3A]"></span>
                    Admin Aktif
                </span>
                <div class="w-8 h-8 rounded-full bg-[#DCE9DF] text-[#1D4E31] font-bold text-xs flex items-center justify-center border border-[#C2D8C8]">
                    RW
                </div>
            </div>
        </header>

        <!-- MAIN WORKSPACE -->
        <main class="flex-1 px-8 py-6 max-w-[1380px] w-full mx-auto">

            <!-- Page Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="text-[11px] font-bold tracking-wider uppercase text-[#2D6A4F]">
                        PBI-02 • ADMIN
                    </div>
                    <h1 class="text-[28px] font-extrabold text-[#14231A] tracking-tight mt-0.5">
                        Digitasi Batas Kawasan
                    </h1>
                    <p class="text-[13.5px] text-[#5B6B61] mt-1">
                        Gambar dan validasi polygon kawasan secara interaktif atau unggah berkas GeoJSON terstandar.
                    </p>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase bg-[#E6EDF5] text-[#3B5978] border border-[#D2DFEC]">
                        Versi Aktif: {{ $batasAktif ? 'v' . $batasAktif->nomor_versi : 'Belum Ada' }}
                    </span>
                </div>
            </div>

            <!-- Flash Messages (Feedback Validasi / Sukses dari Server) -->
            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-[#059669] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] text-sm flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-[#DC2626] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <span class="font-bold">Validasi Ditolak:</span> {{ session('error') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] text-sm shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 2-COLUMN MAIN GRID (Sesuai Mockup Figma 01_Digitasi_Batas_Kawasan) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- KOLOM KIRI (7/12): EDITOR POLYGON & IMPOR GEOJSON -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E1E7E0] p-6 shadow-[0_1px_3px_rgba(16,24,20,0.03)]">
                    <!-- Card Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-[17px] font-bold text-[#14231A]">Editor polygon</h2>
                            <!-- Mode Switcher Pill (Digitasi vs Impor GeoJSON) -->
                            <div class="inline-flex rounded-lg bg-[#F1F5F1] p-0.5 border border-[#E1E7E0] text-xs font-medium">
                                <button type="button"
                                        @click="mode = 'digitasi'"
                                        :class="mode === 'digitasi' ? 'bg-white text-[#14231A] shadow-xs font-semibold' : 'text-[#5B6B61] hover:text-[#14231A]'"
                                        class="px-2.5 py-1 rounded-md transition">
                                    Digitasi Peta
                                </button>
                                <button type="button"
                                        @click="mode = 'impor'"
                                        :class="mode === 'impor' ? 'bg-white text-[#14231A] shadow-xs font-semibold' : 'text-[#5B6B61] hover:text-[#14231A]'"
                                        class="px-2.5 py-1 rounded-md transition">
                                    Impor GeoJSON
                                </button>
                            </div>
                        </div>

                        <span class="px-3 py-1 rounded-full text-[11px] font-bold tracking-wide bg-[#EAF0F6] text-[#3B5978]">
                            WGS84 • SRID 4326
                        </span>
                    </div>

                    <!-- MODE 1: PETA DIGITASI INTERAKTIF -->
                    <div x-show="mode === 'digitasi'">
                        <!-- Container Peta -->
                        <div class="relative w-full h-[400px] rounded-xl overflow-hidden border border-[#DCE3DC] bg-[#EAECE6]">
                            <div id="map-digitasi" class="w-full h-full"></div>

                            <!-- Floating Toolbar Kiri Atas (+ Tambah titik | Undo | Reset) -->
                            <div class="absolute top-3.5 left-3.5 z-[500] inline-flex items-center bg-white/95 backdrop-blur-xs rounded-xl shadow-md border border-[#DCE3DC] divide-x divide-[#E5EAE5] text-xs font-semibold text-[#16231B]">
                                <button type="button"
                                        @click="toggleDrawMode()"
                                        :class="isDrawing ? 'bg-[#E6F2EA] text-[#235D3A]' : 'hover:bg-[#F7F9F7]'"
                                        class="px-3.5 py-2 rounded-l-xl flex items-center gap-1.5 transition">
                                    <span class="text-sm font-bold leading-none">+</span>
                                    <span x-text="isDrawing ? 'Selesai klik peta' : 'Tambah titik'"></span>
                                </button>
                                <button type="button"
                                        @click="undoPoint()"
                                        :disabled="points.length === 0"
                                        class="px-3.5 py-2 hover:bg-[#F7F9F7] disabled:opacity-40 transition">
                                    Undo
                                </button>
                                <button type="button"
                                        @click="resetPolygon()"
                                        :disabled="points.length === 0"
                                        class="px-3 py-2 rounded-r-xl text-[#8A4B38] hover:bg-[#FEF2F2] disabled:opacity-40 transition">
                                    Reset
                                </button>
                            </div>

                            <!-- Floating Status Pill Kiri Bawah -->
                            <div class="absolute bottom-3.5 left-3.5 z-[500] px-3.5 py-1.5 rounded-full bg-white/95 backdrop-blur-xs shadow-sm border border-[#DCE3DC] text-xs font-medium text-[#2A3B31]">
                                Polygon utama <span class="mx-1 text-[#9AA89F]">•</span>
                                <span class="font-bold text-[#14231A]" x-text="points.length"></span> titik batas aktif
                            </div>
                        </div>

                        <!-- Baris Legenda -->
                        <div class="flex items-center justify-between mt-3.5 text-xs text-[#5B6B61]">
                            <div class="flex items-center gap-5">
                                <span class="font-semibold text-[#3C4B42]">Legenda</span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#235D3A]"></span>
                                    Batas kawasan
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#D6C79C]"></span>
                                    Jalan / Referensi dasar
                                </span>
                            </div>
                            <span class="text-[11px] text-[#7A8A80]">Klik peta untuk tambah titik • Geser titik untuk ubah bentuk</span>
                        </div>

                        <!-- Action Buttons Footer Card Kiri -->
                        <div class="grid grid-cols-2 gap-3.5 mt-5 pt-4 border-t border-[#EDF1ED]">
                            <button type="button"
                                    @click="submitDigitasi('draf')"
                                    class="w-full py-2.5 px-4 rounded-xl border border-[#CBD5CC] bg-white hover:bg-[#F7F9F7] text-[#1D3528] text-[13.5px] font-semibold transition">
                                Simpan sebagai draf
                            </button>

                            <button type="button"
                                    @click="validateGeometry()"
                                    class="w-full py-2.5 px-4 rounded-xl bg-[#235D3A] hover:bg-[#1B492D] text-white text-[13.5px] font-semibold flex items-center justify-center gap-2 shadow-xs transition">
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

                            <div class="border-2 border-dashed border-[#C7D5C9] rounded-2xl bg-[#F8FAF8] p-8 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-[#E4EFE7] text-[#235D3A] flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <h3 class="text-sm font-bold text-[#14231A]">Unggah Berkas Spasial GeoJSON</h3>
                                <p class="text-xs text-[#5B6B61] max-w-md mx-auto mt-1">
                                    Mendukung format <code class="font-semibold text-[#235D3A]">.geojson</code> atau <code class="font-semibold text-[#235D3A]">.json</code> dengan tipe geometri <strong>Polygon</strong> atau <strong>MultiPolygon</strong> (WGS 84 / EPSG:4326, maks. 5 MB).
                                </p>

                                <div class="mt-5">
                                    <input type="file"
                                           name="file_geojson"
                                           accept=".geojson,.json,application/geo+json,application/json"
                                           required
                                           class="block w-full max-w-sm mx-auto text-xs text-[#3C4B42] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#235D3A] file:text-white hover:file:bg-[#1B492D] cursor-pointer">
                                </div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#F4F7F4] border border-[#E1E7E0] text-xs text-[#4C5C52]">
                                <strong>Jaminan Transaksi Atomik:</strong> Berkas yang gagal validasi format, CRS, atau topologi (<code class="text-[#235D3A]">ST_IsValid</code>) akan ditolak sepenuhnya tanpa menyisakan data parsial di database.
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button"
                                        @click="mode = 'digitasi'"
                                        class="py-2.5 px-4 rounded-xl border border-[#CBD5CC] text-xs font-semibold text-[#1D3528] hover:bg-[#F7F9F7]">
                                    Kembali ke Peta
                                </button>
                                <button type="submit"
                                        class="py-2.5 px-5 rounded-xl bg-[#235D3A] hover:bg-[#1B492D] text-white text-xs font-semibold shadow-xs transition">
                                    Unggah &amp; Validasi GeoJSON
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN (5/12): INFORMASI KAWASAN & PANEL VALIDASI -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E1E7E0] p-6 shadow-[0_1px_3px_rgba(16,24,20,0.03)]">
                    <!-- Card Header -->
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-[17px] font-bold text-[#14231A]">Informasi kawasan</h2>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase bg-[#E5F2E8] text-[#235D3A]">
                            {{ $kawasan->kode_wilayah ?? 'AREA-' . str_pad($kawasan->kawasan_id, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <!-- Form Hidden untuk Simpan Digitasi ke Server -->
                    <form id="form-digitasi" action="{{ route('admin.kawasan.batas.digitasi', $kawasan->kawasan_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="geojson_digitasi" x-model="geojsonString">
                        <input type="hidden" name="mode_simpan" x-model="modeSimpan">

                        <div class="space-y-4">
                            <!-- Nama Kawasan -->
                            <div>
                                <label class="block text-xs font-medium text-[#5B6B61] mb-1.5">Nama kawasan</label>
                                <div class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAF8] border border-[#E1E7E0] text-[13.5px] font-medium text-[#14231A]">
                                    {{ $kawasan->nama_kawasan }}
                                </div>
                            </div>

                            <!-- Kategori & Wilayah Pilot -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-[#5B6B61] mb-1.5">Kategori</label>
                                    <div class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAF8] border border-[#E1E7E0] text-[13px] font-medium text-[#14231A] truncate">
                                        {{ $kawasan->jenis_kawasan ?? 'Kawasan Konservasi' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[#5B6B61] mb-1.5">Wilayah pilot</label>
                                    <div class="w-full px-3.5 py-2.5 rounded-xl bg-[#F8FAF8] border border-[#E1E7E0] text-[13px] font-medium text-[#14231A] truncate">
                                        {{ $kawasan->nama_wilayah ?? 'Wilayah Utama' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Sumber Batas -->
                            <div>
                                <label for="sumber_id" class="block text-xs font-medium text-[#5B6B61] mb-1.5">Sumber batas</label>
                                <select id="sumber_id"
                                        name="sumber_id"
                                        x-model="selectedSumberId"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#DCE3DC] text-[13px] font-medium text-[#14231A] focus:outline-none focus:border-[#235D3A]">
                                    <option value="">Digitasi internal — data eksisting</option>
                                    @foreach ($daftarSumber as $sumber)
                                        <option value="{{ $sumber->sumber_id }}" @selected($kawasan->sumber_id == $sumber->sumber_id)>
                                            {{ $sumber->nama_sumber }} ({{ $sumber->kode_crs }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3 KARTU METRIK SEJAJAR (Jumlah titik, Luas estimasi, Validasi) -->
                            <div class="grid grid-cols-3 gap-2.5 pt-1">
                                <div class="p-3 rounded-xl bg-[#F8FAF8] border border-[#E5EAE5]">
                                    <div class="text-[11px] font-medium text-[#64746B]">Jumlah titik</div>
                                    <div class="text-xl font-extrabold text-[#14231A] mt-1" x-text="points.length"></div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#F8FAF8] border border-[#E5EAE5]">
                                    <div class="text-[11px] font-medium text-[#64746B]">Luas estimasi</div>
                                    <div class="text-xl font-extrabold text-[#14231A] mt-1">
                                        <span x-text="formattedArea"></span> <span class="text-sm font-bold">ha</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#F8FAF8] border border-[#E5EAE5]">
                                    <div class="text-[11px] font-medium text-[#64746B]">Validasi</div>
                                    <div class="text-xl font-extrabold mt-1"
                                         :class="{
                                             'text-[#B7791F]': validationStatus === 'Belum',
                                             'text-[#235D3A]': validationStatus === 'Valid',
                                             'text-[#C53030]': validationStatus === 'Gagal'
                                         }"
                                         x-text="validationStatus">
                                    </div>
                                </div>
                            </div>

                            <!-- KOTAK STATUS VALIDASI DINAMIS -->
                            <!-- State 1: Belum Validasi (Kuning Lembut seperti Figma) -->
                            <div x-show="validationStatus === 'Belum'"
                                 class="p-3.5 rounded-xl bg-[#FEF9EC] border border-[#F5DFA4]">
                                <div class="text-xs font-bold text-[#975A16]">Perlu validasi</div>
                                <p class="text-[11.5px] text-[#975A16]/90 mt-0.5 leading-relaxed">
                                    Validasi geometri, batas wilayah, CRS, dan luas nyata perlu diperiksa sebelum disimpan.
                                </p>
                            </div>

                            <!-- State 2: Valid (Hijau Lembut) -->
                            <div x-show="validationStatus === 'Valid'" x-cloak
                                 class="p-3.5 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0]">
                                <div class="text-xs font-bold text-[#065F46]">Geometri siap disimpan</div>
                                <p class="text-[11.5px] text-[#047857] mt-0.5 leading-relaxed" x-text="validationMessage"></p>
                            </div>

                            <!-- State 3: Gagal Validasi (Merah Lembut) -->
                            <div x-show="validationStatus === 'Gagal'" x-cloak
                                 class="p-3.5 rounded-xl bg-[#FEF2F2] border border-[#FECACA]">
                                <div class="text-xs font-bold text-[#991B1B]">Geometri tidak valid</div>
                                <p class="text-[11.5px] text-[#B91C1C] mt-0.5 leading-relaxed" x-text="validationMessage"></p>
                            </div>

                            <!-- ATURAN VALIDASI -->
                            <div class="pt-1">
                                <div class="text-xs font-bold text-[#14231A] mb-2">Aturan validasi</div>
                                <ul class="space-y-1.5 text-xs text-[#4C5C52]">
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#235D3A]"></span>
                                        <span>Minimal tiga titik, area tidak nol</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#235D3A]"></span>
                                        <span>Tidak ada polygon yang tumpang tindih sendiri</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#235D3A]"></span>
                                        <span>Jangan menganggap batas demo legal</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- FOOTER TOMBOL KANAN (Validasi ulang & Simpan batas) -->
                            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-[#EDF1ED]">
                                <button type="button"
                                        @click="validateGeometry()"
                                        class="w-full py-2.5 px-4 rounded-xl bg-[#235D3A] hover:bg-[#1B492D] text-white text-[13.5px] font-semibold transition shadow-xs">
                                    Validasi ulang
                                </button>

                                <button type="button"
                                        @click="submitDigitasi('final')"
                                        :class="validationStatus === 'Valid'
                                            ? 'bg-[#143726] hover:bg-[#0E281B] text-white shadow-xs'
                                            : 'bg-[#E5ECE6] hover:bg-[#D7E2D9] text-[#354A3E]'"
                                        class="w-full py-2.5 px-4 rounded-xl text-[13.5px] font-semibold transition">
                                    Simpan batas
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIWAYAT VERSI BATAS KAWASAN (Versioning Audit Trail ERD) -->
            <div class="mt-6 bg-white rounded-2xl border border-[#E1E7E0] p-6 shadow-[0_1px_3px_rgba(16,24,20,0.03)]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-[#14231A]">Riwayat Versi Batas Kawasan</h3>
                        <p class="text-xs text-[#5B6B61]">Setiap perubahan digitasi atau impor GeoJSON disimpan sebagai versi baru tanpa menimpa riwayat lama.</p>
                    </div>
                    <span class="text-xs font-semibold text-[#235D3A] bg-[#E5F2E8] px-3 py-1 rounded-full">
                        Total {{ count($riwayatVersi) }} versi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-[#E5EAE5] text-[#64746B] uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3 font-semibold">Versi</th>
                                <th class="py-2.5 px-3 font-semibold">CRS</th>
                                <th class="py-2.5 px-3 font-semibold">Luas Estimasi</th>
                                <th class="py-2.5 px-3 font-semibold">Status Validasi</th>
                                <th class="py-2.5 px-3 font-semibold">Catatan Validasi</th>
                                <th class="py-2.5 px-3 font-semibold">Dibuat Oleh</th>
                                <th class="py-2.5 px-3 font-semibold">Waktu Simpan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2EE]">
                            @forelse ($riwayatVersi as $versi)
                                <tr class="hover:bg-[#F9FBF9]">
                                    <td class="py-3 px-3 font-bold text-[#14231A]">v{{ $versi->nomor_versi }}</td>
                                    <td class="py-3 px-3 text-[#4C5C52]">{{ $versi->kode_crs }}</td>
                                    <td class="py-3 px-3 font-semibold text-[#14231A]">{{ number_format((float) $versi->luas_hektar, 2, ',', '.') }} ha</td>
                                    <td class="py-3 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E5F2E8] text-[#235D3A]">
                                            {{ $versi->status_validasi }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-[#4C5C52]">{{ $versi->pesan_validasi }}</td>
                                    <td class="py-3 px-3 text-[#4C5C52]">{{ $versi->pembuat ?? 'Admin Sistem' }}</td>
                                    <td class="py-3 px-3 text-[#64746B]">{{ \Carbon\Carbon::parse($versi->dibuat_pada)->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-[#64746B]">
                                        Belum ada riwayat batas tersimpan untuk kawasan ini. Klik peta di atas untuk mulai digitasi atau impor file GeoJSON.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
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
                    // Koordinat default contoh area hutan/konservasi Indonesia (Riau / Kalimantan)
                    const defaultCenter = [-0.5071, 101.4478];
                    this.map = L.map('map-digitasi', {
                        zoomControl: false
                    }).setView(defaultCenter, 14);

                    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                    // Basemap terang bersih menyerupai peta Figma
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    // Muat poligon eksisting jika sudah ada di database
                    if (initialGeojson) {
                        try {
                            const parsed = JSON.parse(initialGeojson);
                            const coords = parsed.type === 'MultiPolygon'
                                ? parsed.coordinates[0][0]
                                : parsed.coordinates[0];

                            if (Array.isArray(coords) && coords.length > 3) {
                                // Hapus titik penutup terakhir karena Leaflet otomatis menutup ring
                                const ring = coords.slice(0, -1);
                                this.points = ring.map(c => [c[1], c[0]]);
                                this.isDrawing = false;
                                this.renderPolygon(true);
                            }
                        } catch (e) {
                            console.error('Gagal mem-parsing GeoJSON awal:', e);
                        }
                    }

                    // Event klik peta untuk menambah titik vertex
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
                            color: '#235D3A',
                            weight: 2.5,
                            dashArray: '6, 6',
                            fillColor: '#52B788',
                            fillOpacity: 0.24
                        }).addTo(this.map);

                        this.syncGeojsonInput();
                    } else if (this.points.length === 2) {
                        this.polygonLayer = L.polyline(this.points, {
                            color: '#235D3A',
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
                    // Konversi [lat, lng] ke standar GeoJSON [lng, lat] dan tutup ring
                    const ring = this.points.map(p => [Number(p[1].toFixed(7)), Number(p[0].toFixed(7))]);
                    ring.push(ring[0]);

                    const geojson = {
                        type: 'Polygon',
                        coordinates: [ring]
                    };
                    this.geojsonString = JSON.stringify(geojson);
                },

                // Menghitung luas geodesik dalam Hektar (ha)
                get areaHectares() {
                    if (this.points.length < 3) return 0;
                    const R = 6378137; // Radius bumi (meter)
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

                // Pengecekan perpotongan antar segmen garis poligon
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
</body>
</html>