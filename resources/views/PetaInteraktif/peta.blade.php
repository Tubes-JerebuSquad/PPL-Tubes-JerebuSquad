@extends('layouts.app')

@section('title', 'Peta Interaktif Multi-Layer — RimbaWatch')
@section('breadcrumb', 'Peta Interaktif')

@push('styles')
    <!-- Leaflet.js v1.9.4 & MarkerCluster v1.5.3 CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Stylesheet Peta Terpisah -->
    <link rel="stylesheet" href="{{ asset('css/peta.css') }}" />
@endpush

@section('content')
@php
    $user = auth()->user();
    $roleSlug = $roleSlug ?? 'masyarakat';
    $roleLabel = $roleLabel ?? 'Masyarakat';
    $namaLengkap = $namaLengkap ?? ($user?->nama_lengkap ?? 'Pengguna Publik');
    $inisial = $inisial ?? strtoupper(substr($namaLengkap, 0, 2));

    if (!isset($roleSlug) || empty($roleSlug)) {
        if ($user) {
            if ($user->isAdmin()) {
                $roleSlug = 'admin';
                $roleLabel = 'Administrator';
            } elseif ($user->isPengawas()) {
                $roleSlug = 'pengawas';
                $roleLabel = 'Pengawas';
            } elseif ($user->isPetugasLapangan()) {
                $roleSlug = 'petugas';
                $roleLabel = 'Petugas Lapangan';
            } else {
                $roleSlug = 'masyarakat';
                $roleLabel = 'Masyarakat';
            }
        }
    }
@endphp

<div class="space-y-5">
    <!-- Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
        <div>
            <div class="text-[11px] font-bold tracking-wider text-rimba-700 uppercase">GIS / EXPLORER</div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-ink-900 tracking-tight mt-0.5">Peta interaktif multi-layer</h1>
            <p class="text-xs sm:text-sm text-ink-500 mt-1">Jelajahi batas kawasan, hotspot indikatif, laporan yang diizinkan, dan kegiatan konservasi.</p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
            <button type="button" onclick="RW_GIS.openAdvancedModal('filter')" class="btn-primary text-xs sm:text-[13px] px-4 py-2.5 rounded-xl shadow-sm transition">
                <span>Aksi lanjutan</span>
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 ml-1"></i>
            </button>
        </div>
    </div>

    <!-- Status Strip + Quick Regional Filter Pills -->
    <div class="bg-rimba-100/70 border border-rimba-200 rounded-xl px-4 py-3 flex flex-col xl:flex-row xl:items-center justify-between gap-2.5 text-xs text-rimba-900">
        <div class="flex items-center gap-2 font-medium">
            <i data-lucide="shield-check" class="w-4 h-4 text-rimba-700 shrink-0 stroke-[2.2]"></i>
            <span>Sistem Informasi Geospasial Aktif · Data resmi KLHK, Sipongi, dan verifikasi lapangan terintegrasi.</span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
            <button type="button" onclick="RW_GIS.setQuickRegionFilter('all')" id="reg-all" class="px-3 py-1 rounded-full bg-rimba-900 text-white font-semibold transition">Semua Wilayah</button>
            <button type="button" onclick="RW_GIS.setQuickRegionFilter('Kab. Cianjur')" id="reg-cianjur" class="px-3 py-1 rounded-full bg-white text-ink-700 border border-rimba-200 hover:bg-white font-semibold transition">Kab. Cianjur</button>
            <button type="button" onclick="RW_GIS.setQuickRegionFilter('Kab. Bogor')" id="reg-bogor" class="px-3 py-1 rounded-full bg-white text-ink-700 border border-rimba-200 hover:bg-white font-semibold transition">Kab. Bogor</button>
            <button type="button" onclick="RW_GIS.setQuickRegionFilter('Kab. Sukabumi')" id="reg-sukabumi" class="px-3 py-1 rounded-full bg-white text-ink-700 border border-rimba-200 hover:bg-white font-semibold transition">Kab. Sukabumi</button>
        </div>
    </div>

    <!-- TWO-COLUMN WORKSPACE GRID (Map Left ~70%, Control Cards Right ~30%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- LEFT COLUMN: OPENSTREETMAP CANVAS CARD (~70%) -->
        <div class="lg:col-span-8 xl:col-span-9 flex flex-col">
            <div class="bg-white rounded-2xl border border-line p-2.5 sm:p-3 shadow-sm">
                <div class="relative rounded-xl overflow-hidden border border-line h-[480px] sm:h-[540px] lg:h-[620px]">
                    <!-- Leaflet OpenStreetMap Canvas -->
                    <div id="map"></div>

                    <!-- Top-Left Custom Controls: Zoom +, Zoom -, Reset Extent, Geolocate -->
                    <div class="absolute top-3 left-3 z-[500] flex flex-col gap-1.5">
                        <div class="bg-white/95 backdrop-blur-xs rounded-xl border border-line shadow-sm overflow-hidden flex flex-col">
                            <button type="button" onclick="map.zoomIn()" title="Perbesar Peta (+)" class="w-8 h-8 flex items-center justify-center text-ink-700 hover:bg-rimba-50 border-b border-line font-bold text-base">+</button>
                            <button type="button" onclick="map.zoomOut()" title="Perkecil Peta (-)" class="w-8 h-8 flex items-center justify-center text-ink-700 hover:bg-rimba-50 font-bold text-base">&minus;</button>
                        </div>
                        <button type="button" onclick="RW_GIS.resetMapExtent()" title="Reset Extent ke Wilayah Fokus" class="w-8 h-8 bg-white/95 backdrop-blur-xs rounded-xl border border-line shadow-sm flex items-center justify-center text-ink-700 hover:bg-rimba-50 transition">
                            <i data-lucide="maximize-2" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="RW_GIS.geolocateUser()" title="Deteksi Lokasi GPS Perangkat" class="w-8 h-8 bg-white/95 backdrop-blur-xs rounded-xl border border-line shadow-sm flex items-center justify-center text-ink-700 hover:bg-rimba-50 transition">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Top-Right Basemap Switcher (Satelit, OSM Minimalis, OSM Standar) -->
                    <div class="absolute top-3 right-3 z-[500] flex items-center gap-1 bg-white/95 backdrop-blur-xs p-1 rounded-xl border border-line shadow-sm text-[11px] font-semibold">
                        <button type="button" onclick="RW_GIS.setBasemap('satelit')" id="bm-satelit" class="px-2.5 py-1 rounded-lg text-ink-500 hover:bg-rimba-50 transition flex items-center gap-1.5" title="Beralih ke Citra Satelit Resolusi Tinggi">
                            <i data-lucide="satellite" class="w-3.5 h-3.5"></i>
                            <span>Satelit</span>
                        </button>
                        <button type="button" onclick="RW_GIS.setBasemap('minimalis')" id="bm-minimalis" class="px-2.5 py-1 rounded-lg bg-rimba-900 text-white transition flex items-center gap-1.5" title="Beralih ke OSM Tampilan Minimalis">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            <span>Minimalis</span>
                        </button>
                        <button type="button" onclick="RW_GIS.setBasemap('standar')" id="bm-standar" class="px-2.5 py-1 rounded-lg text-ink-500 hover:bg-rimba-50 transition flex items-center gap-1.5" title="Beralih ke OSM Penuh">
                            <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                            <span>Standar</span>
                        </button>
                    </div>

                    <!-- Bottom-Left Map Info Badge -->
                    <div class="absolute bottom-3 left-3 z-[500] pointer-events-none flex flex-wrap items-center gap-1.5">
                        <span class="inline-block px-2.5 py-1 rounded-md bg-white/95 backdrop-blur-xs text-[10px] font-bold tracking-wider text-rimba-900 uppercase border border-line shadow-2xs">
                            PETA GEOSPASIAL RESMI · POSTGIS ENGINE
                        </span>
                        <span class="hidden sm:inline-block px-2.5 py-1 rounded-md bg-white/95 backdrop-blur-xs text-[10px] font-medium text-ink-500 border border-line shadow-2xs">
                            Sinkronisasi: {{ now()->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Under Map -->
            <div class="mt-2.5 px-1 flex flex-wrap items-center justify-between gap-2 text-[10px] font-bold tracking-wider text-ink-400 uppercase">
                <span>RIMBAWATCH · SISTEM INFORMASI PEMANTAUAN DEFORESTASI &amp; KONSERVASI</span>
                <span id="viewportCoords" class="font-mono font-medium text-ink-400">6.7850° S, 107.1250° E · Z12</span>
            </div>
        </div>

        <!-- RIGHT COLUMN: 3 STACKED CARDS (~30%) -->
        <div class="lg:col-span-4 xl:col-span-3 flex flex-col gap-4">

            <!-- CARD 1: LAYER PETA -->
            <section class="bg-white rounded-2xl border border-line p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3.5">
                    <h2 class="text-sm font-bold text-ink-900">Layer peta</h2>
                    <span id="activeLayerCount" class="text-[11px] font-medium text-ink-400">4/4 aktif</span>
                </div>

                <div class="space-y-3 text-[13px]">
                    <label class="flex items-center justify-between cursor-pointer group select-none">
                        <span class="flex items-center gap-2.5">
                            <input type="checkbox" id="layer-batas" checked onchange="RW_GIS.toggleMapLayer('batas', this.checked)" class="rw-checkbox" />
                            <span class="font-medium text-ink-800 group-hover:text-rimba-900">Batas kawasan</span>
                        </span>
                        <span id="count-batas" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-rimba-50 text-ink-700 border border-line">4</span>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer group select-none">
                        <span class="flex items-center gap-2.5">
                            <input type="checkbox" id="layer-laporan" checked onchange="RW_GIS.toggleMapLayer('laporan', this.checked)" class="rw-checkbox" />
                            <span class="font-medium text-ink-800 group-hover:text-rimba-900">Laporan indikasi</span>
                        </span>
                        <span id="count-laporan" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-rimba-50 text-ink-700 border border-line">5</span>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer group select-none">
                        <span class="flex items-center gap-2.5">
                            <input type="checkbox" id="layer-hotspot" checked onchange="RW_GIS.toggleMapLayer('hotspot', this.checked)" class="rw-checkbox" />
                            <span class="font-medium text-ink-800 group-hover:text-rimba-900">Titik panas</span>
                        </span>
                        <span id="count-hotspot" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-rimba-50 text-ink-700 border border-line">5</span>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer group select-none">
                        <span class="flex items-center gap-2.5">
                            <input type="checkbox" id="layer-konservasi" checked onchange="RW_GIS.toggleMapLayer('konservasi', this.checked)" class="rw-checkbox" />
                            <span class="font-medium text-ink-800 group-hover:text-rimba-900">Konservasi</span>
                        </span>
                        <span id="count-konservasi" class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-rimba-50 text-ink-700 border border-line">4</span>
                    </label>
                </div>

                <!-- Basemap Switcher di Panel Kontrol -->
                <div class="mt-4 pt-3.5 border-t border-line">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-ink-600 uppercase tracking-wider">Tipe Peta Dasar</span>
                        <span class="text-[10px] text-ink-400">Esri / OSM</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 text-xs font-semibold">
                        <button type="button" onclick="RW_GIS.setBasemap('satelit')" id="side-bm-satelit" class="py-2 px-1 rounded-xl border border-line hover:bg-rimba-50 text-ink-600 transition flex flex-col items-center justify-center gap-1 text-center" title="Beralih ke Citra Satelit Resolusi Tinggi">
                            <i data-lucide="satellite" class="w-4 h-4"></i>
                            <span class="text-[11px]">Satelit</span>
                        </button>
                        <button type="button" onclick="RW_GIS.setBasemap('minimalis')" id="side-bm-minimalis" class="py-2 px-1 rounded-xl bg-rimba-900 text-white transition flex flex-col items-center justify-center gap-1 text-center" title="Beralih ke OSM Tampilan Minimalis">
                            <i data-lucide="map" class="w-4 h-4"></i>
                            <span class="text-[11px]">Minimalis</span>
                        </button>
                        <button type="button" onclick="RW_GIS.setBasemap('standar')" id="side-bm-standar" class="py-2 px-1 rounded-xl border border-line hover:bg-rimba-50 text-ink-600 transition flex flex-col items-center justify-center gap-1 text-center" title="Beralih ke OSM Penuh">
                            <i data-lucide="compass" class="w-4 h-4"></i>
                            <span class="text-[11px]">Standar</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- CARD 2: FITUR TERPILIH & LEGENDA DINAMIS -->
            <section class="bg-white rounded-2xl border border-line p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h2 class="text-sm font-bold text-ink-900">Fitur terpilih</h2>
                        <span id="selectedFeatureTypeBadge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-rimba-100 text-rimba-800">Batas Kawasan</span>
                    </div>

                    <!-- Key-Value Rows & Role-Specific Attributes -->
                    <div id="featureInspectorContent" class="space-y-3 text-[13px]"></div>

                    <!-- Integrated Legenda Dinamis Section -->
                    <div class="mt-5 pt-4 border-t border-line">
                        <div class="flex items-center justify-between mb-2.5">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-ink-400">Legenda dinamis</h3>
                            <span class="text-[10px] text-ink-400">Otomatis</span>
                        </div>
                        <div id="dynamicLegendList" class="space-y-2 text-xs text-ink-700"></div>
                    </div>
                </div>
            </section>

            <!-- CARD 3: CLICKABLE DISCLAIMER PILL (PRD SECTION 2) -->
            <button type="button" onclick="RW_GIS.openDisclaimerModal()" class="w-full text-center bg-[#FDF6E7] hover:bg-[#F9EDCD] border border-[#F3E2BE] rounded-2xl px-4 py-3.5 flex items-center justify-center gap-2 shadow-sm transition group">
                <span class="text-xs sm:text-[13px] font-bold text-[#8A6116] group-hover:underline">Indikasi &ne; konfirmasi</span>
            </button>

        </div>
    </div>
</div>

<!-- MODAL OVERLAY -->
<div id="modalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-[1px] z-[1000] hidden items-center justify-center p-4" onclick="if(event.target===this) RW_GIS.closeModal()">
    <div class="bg-white rounded-2xl border border-line max-w-xl w-full p-5 sm:p-6 shadow-xl">
        <div class="flex items-start justify-between gap-3 pb-3 border-b border-line">
            <div>
                <span id="modalSubtitle" class="text-[10px] font-bold uppercase tracking-wider text-rimba-700">RIMBAWATCH · PBI-04</span>
                <h3 id="modalTitle" class="text-base sm:text-lg font-bold text-ink-900 mt-0.5">Judul Modal</h3>
            </div>
            <button type="button" onclick="RW_GIS.closeModal()" class="p-1.5 rounded-lg text-ink-400 hover:text-ink-700 hover:bg-rimba-50 transition" aria-label="Tutup modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div id="modalBody" class="mt-4 text-xs sm:text-[13px] text-ink-700 space-y-3.5 max-h-[68vh] overflow-y-auto pr-1"></div>
        <div id="modalFooter" class="mt-5 pt-3 border-t border-line flex items-center justify-end gap-2">
            <button type="button" onclick="RW_GIS.closeModal()" class="btn-secondary text-xs">Tutup</button>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-[1100] flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4 sm:px-0"></div>

@endsection

@push('scripts')
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Leaflet v1.9.4 & MarkerCluster v1.5.3 JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <!-- Konfigurasi Server ke Frontend JS -->
    <script>
        window.RW_CONFIG = {
            role: @json($roleSlug),
            roleLabel: @json($roleLabel),
            userName: @json($namaLengkap),
            csrfToken: @json(csrf_token()),
        };
    </script>

    <!-- Script Peta Terpisah -->
    <script src="{{ asset('js/peta.js') }}"></script>
@endpush
