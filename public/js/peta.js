/**
 * ============================================================================
 * RIMBAWATCH — PETA INTERAKTIF MULTI-LAYER (PBI-04 / FR-04)
 * JavaScript Logika & Interaktivitas GIS Terpisah (peta.js)
 * ============================================================================
 */

(function () {
  'use strict';

  // 1. APPLICATION STATE
  const cfg = window.RW_CONFIG || {};
  const currentRole = cfg.role || 'admin';

  const appState = {
    role: currentRole, // 'admin' | 'pengawas' | 'petugas' | 'masyarakat'
    layers: {
      batas: true,
      laporan: true,
      hotspot: true,
      konservasi: true
    },
    filters: {
      wilayah: 'all',
      statusLaporan: 'all',
      hotspotFreshness: 'all'
    },
    osmStyle: 'minimalis', // 'minimalis' | 'standar'
    selectedFeatureId: 'kw-01', // Default selected: Kawasan Konservasi Cianjur Utara (1.284 ha)
    activeModalTab: 'filter'
  };

  const ROLE_META = {
    admin: {
      label: 'Administrator',
      initials: 'AD',
      description: 'Akses penuh seluruh layer spasial termasuk poligon kawasan Nonaktif/Arsip, nomor versi batas, status validasi geometri, dan audit metadata.'
    },
    pengawas: {
      label: 'Pengawas',
      initials: 'PG',
      description: 'Akses penuh seluruh layer operasional, semua status triase laporan indikasi, kelas keyakinan hasil inspeksi, dan realisasi konservasi.'
    },
    petugas: {
      label: 'Petugas Lapangan',
      initials: 'PL',
      description: 'Akses kawasan aktif, titik panas, laporan indikasi yang ditugaskan untuk inspeksi lapangan, dan kegiatan konservasi tanggung jawabnya.'
    },
    masyarakat: {
      label: 'Masyarakat',
      initials: 'MS',
      description: 'Akses kawasan aktif, hotspot indikasi awal, laporan milik sendiri (identitas pelapor lain disamarkan sesuai NF-03), dan konservasi terbuka.'
    }
  };

  // 2. GEOSPATIAL DATASET (Batas Kawasan, Titik Panas, Laporan Indikasi, Konservasi)
  const BATAS_KAWASAN_GEOJSON = {
    type: 'FeatureCollection',
    features: [
      {
        type: 'Feature',
        properties: {
          id: 'kw-01',
          layer: 'batas',
          nama: 'Kawasan Konservasi Cianjur Utara',
          wilayah: 'Kab. Cianjur',
          luas: '1.284 ha',
          sumber: 'Geoportal KLHK / BPKH',
          provenance: 'Ditjen PKTL KLHK · Penetapan Tata Batas SK.712/MENLHK/2026',
          status: 'Aktif',
          nomor_versi: 'v2.1',
          status_validasi: 'Valid',
          catatan_teknis: 'Zona penyangga Gede-Pangrango & perlindungan daerah tangkapan air hulu Citarum-Cisokan.'
        },
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [107.048, -6.735],
            [107.108, -6.722],
            [107.136, -6.805],
            [107.094, -6.855],
            [107.054, -6.815],
            [107.048, -6.735]
          ]]
        }
      },
      {
        type: 'Feature',
        properties: {
          id: 'kw-02',
          layer: 'batas',
          nama: 'Koridor Hijau Cianjur Timur',
          wilayah: 'Kab. Cianjur',
          luas: '940 ha',
          sumber: 'Geoportal KLHK / BPKH',
          provenance: 'Ditjen KSDAE · Deliniasi Koridor Satwa & Hutan Lindung 2026',
          status: 'Aktif',
          nomor_versi: 'v1.4',
          status_validasi: 'Valid',
          catatan_teknis: 'Koridor ekologis penghubung tegakan hutan lindung dan sempadan sungai Cianjur-Sukabumi.'
        },
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [107.138, -6.748],
            [107.185, -6.732],
            [107.202, -6.822],
            [107.164, -6.855],
            [107.138, -6.748]
          ]]
        }
      },
      {
        type: 'Feature',
        properties: {
          id: 'kw-03',
          layer: 'batas',
          nama: 'Zona Penyangga Selatan Lama',
          wilayah: 'Kab. Cianjur',
          luas: '415 ha',
          sumber: 'Arsip BPKH Wilayah XI',
          provenance: 'BPKH Wilayah XI Jawa-Madura · Delineasi Historis (Sebelum Revisi v2.1)',
          status: 'Nonaktif',
          nomor_versi: 'v1.0',
          status_validasi: 'Arsip',
          catatan_teknis: 'Batas delineasi lama sebelum revisi tata batas v2.1. Hanya tampil untuk audit Administrator.'
        },
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [107.105, -6.862],
            [107.156, -6.860],
            [107.152, -6.896],
            [107.102, -6.894],
            [107.105, -6.862]
          ]]
        }
      },
      {
        type: 'Feature',
        properties: {
          id: 'kw-04',
          layer: 'batas',
          nama: 'Kawasan Hutan Lindung Puncak Bogor',
          wilayah: 'Kab. Bogor',
          luas: '1.620 ha',
          sumber: 'Geoportal KLHK / BPKH',
          provenance: 'KLHK / Geoportal WebGIS · Pembaruan Resmi 10 Nov 2026',
          status: 'Aktif',
          nomor_versi: 'v3.0',
          status_validasi: 'Valid',
          catatan_teknis: 'Hutan lindung hulu DAS Ciliwung bagian selatan yang berbatasan langsung dengan Cianjur.'
        },
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [106.968, -6.680],
            [107.032, -6.678],
            [107.038, -6.735],
            [106.975, -6.738],
            [106.968, -6.680]
          ]]
        }
      }
    ]
  };

  const POINT_FEATURES = {
    hotspot: [
      {
        id: 'hs-01',
        layer: 'hotspot',
        kode: 'HS-9041',
        judul: 'Anomali Suhu VIIRS NOAA-20',
        wilayah: 'Kab. Cianjur',
        luas: 'Resolusi piksel 375 m',
        sumber: 'NASA FIRMS / Sipongi KLHK',
        provenance: 'Satelit VIIRS NOAA-20 · 17 Nov 2026, 11:20 WIB',
        status: 'Indikasi Awal',
        is_stale: false,
        freshness_text: 'Data Segar (4 jam lalu)',
        confidence: '84%',
        brightness: '334,6 K',
        satellite: 'VIIRS NOAA-20',
        sumber_id: 'SRC-FIRMS-20261117-A',
        waktu_impor: '17 Nov 2026, 12:00 WIB',
        coords: [-6.796, 107.163]
      },
      {
        id: 'hs-02',
        layer: 'hotspot',
        kode: 'HS-8994',
        judul: 'Anomali Suhu MODIS Terra (Usang)',
        wilayah: 'Kab. Cianjur',
        luas: 'Resolusi piksel 1.000 m',
        sumber: 'NASA FIRMS / Sipongi KLHK',
        provenance: 'Satelit MODIS Terra · 15 Nov 2026, 19:15 WIB',
        status: 'Indikasi Awal',
        is_stale: true,
        freshness_text: 'Data Usang (44 jam lalu)',
        confidence: '52%',
        brightness: '318,2 K',
        satellite: 'MODIS Terra',
        sumber_id: 'SRC-MODIS-20261115-C',
        waktu_impor: '15 Nov 2026, 20:05 WIB',
        coords: [-6.752, 107.078]
      },
      {
        id: 'hs-03',
        layer: 'hotspot',
        kode: 'HS-9048',
        judul: 'Anomali Suhu VIIRS Suomi-NPP',
        wilayah: 'Kab. Cianjur',
        luas: 'Resolusi piksel 375 m',
        sumber: 'NASA FIRMS / Sipongi KLHK',
        provenance: 'Satelit VIIRS Suomi-NPP · 17 Nov 2026, 13:05 WIB',
        status: 'Indikasi Awal',
        is_stale: false,
        freshness_text: 'Data Segar (2 jam lalu)',
        confidence: '79%',
        brightness: '329,8 K',
        satellite: 'VIIRS Suomi-NPP',
        sumber_id: 'SRC-FIRMS-20261117-B',
        waktu_impor: '17 Nov 2026, 13:30 WIB',
        coords: [-6.875, 107.128]
      },
      {
        id: 'hs-04',
        layer: 'hotspot',
        kode: 'HS-8972',
        judul: 'Anomali Suhu VIIRS (Usang — Bogor)',
        wilayah: 'Kab. Bogor',
        luas: 'Resolusi piksel 375 m',
        sumber: 'NASA FIRMS / Sipongi KLHK',
        provenance: 'Satelit VIIRS NOAA-20 · 15 Nov 2026, 14:40 WIB',
        status: 'Indikasi Awal',
        is_stale: true,
        freshness_text: 'Data Usang (49 jam lalu)',
        confidence: '61%',
        brightness: '321,4 K',
        satellite: 'VIIRS NOAA-20',
        sumber_id: 'SRC-FIRMS-20261115-A',
        waktu_impor: '15 Nov 2026, 15:10 WIB',
        coords: [-6.708, 106.995]
      },
      {
        id: 'hs-05',
        layer: 'hotspot',
        kode: 'HS-9052',
        judul: 'Anomali Suhu VIIRS Sukabumi',
        wilayah: 'Kab. Sukabumi',
        luas: 'Resolusi piksel 375 m',
        sumber: 'NASA FIRMS / Sipongi KLHK',
        provenance: 'Satelit VIIRS NOAA-20 · 17 Nov 2026, 12:15 WIB',
        status: 'Indikasi Awal',
        is_stale: false,
        freshness_text: 'Data Segar (3 jam lalu)',
        confidence: '88%',
        brightness: '339,1 K',
        satellite: 'VIIRS NOAA-20',
        sumber_id: 'SRC-FIRMS-20261117-B',
        waktu_impor: '17 Nov 2026, 12:45 WIB',
        coords: [-6.845, 106.985]
      }
    ],

    laporan: [
      {
        id: 'lap-01',
        layer: 'laporan',
        kode: 'LAP-2026-081',
        judul: 'Pembukaan Lahan Ilegal Lereng Barat',
        wilayah: 'Kab. Cianjur',
        luas: '± 3,2 ha (estimasi visual)',
        sumber: 'Laporan Masyarakat Terverifikasi',
        provenance: 'Modul Pelaporan RimbaWatch · 15 Nov 2026',
        status: 'Dalam Inspeksi',
        kelas_keyakinan: 'STRONG_INDICATION',
        pelapor_nama: 'Rian Pratama (3203****8812)',
        is_own_report: false,
        assigned_to_petugas: true,
        deskripsi: 'Ditemukan pembukaan tutupan vegetasi baru di lereng barat mendekati batas zona penyangga.',
        coords: [-6.815, 107.110]
      },
      {
        id: 'lap-02',
        layer: 'laporan',
        kode: 'LAP-2026-084',
        judul: 'Penebangan Liar & Jalur Angkut Kayu',
        wilayah: 'Kab. Cianjur',
        luas: '± 1,5 ha (estimasi visual)',
        sumber: 'Laporan Masyarakat (Milik Anda)',
        provenance: 'Modul Pelaporan RimbaWatch · 16 Nov 2026',
        status: 'Perlu Perbaikan',
        kelas_keyakinan: 'UNCERTAIN',
        pelapor_nama: cfg.userName || 'Anda (Laporan Pribadi)',
        is_own_report: true,
        assigned_to_petugas: false,
        deskripsi: 'Foto bukti perlu dilengkapi koordinat sudut pandang lebih jelas sebelum penugasan lapangan.',
        coords: [-6.758, 107.136]
      },
      {
        id: 'lap-03',
        layer: 'laporan',
        kode: 'LAP-2026-089',
        judul: 'Perambahan Hutan Lindung Koridor Timur',
        wilayah: 'Kab. Cianjur',
        luas: '± 2,4 ha',
        sumber: 'Hasil Inspeksi Lapangan',
        provenance: 'Tim Patroli Resort Cianjur · 13 Nov 2026',
        status: 'Terkonfirmasi',
        kelas_keyakinan: 'CONFIRMED',
        pelapor_nama: 'Siti Aminah (3203****4401)',
        is_own_report: false,
        assigned_to_petugas: true,
        deskripsi: 'Hasil pemeriksaan lapangan memvalidasi pembukaan 2,4 ha; patok batas telah dipasang ulang.',
        coords: [-6.782, 107.176]
      },
      {
        id: 'lap-04',
        layer: 'laporan',
        kode: 'LAP-2026-092',
        judul: 'Indikasi Alih Fungsi Sempadan Puncak',
        wilayah: 'Kab. Bogor',
        luas: '± 1,8 ha (estimasi visual)',
        sumber: 'Laporan Masyarakat Terverifikasi',
        provenance: 'Modul Pelaporan RimbaWatch · 17 Nov 2026',
        status: 'Baru',
        kelas_keyakinan: 'UNCERTAIN',
        pelapor_nama: 'Dedi Kurniawan (3201****1190)',
        is_own_report: false,
        assigned_to_petugas: false,
        deskripsi: 'Laporan warga baru masuk mengenai pembersihan lahan semak di batas hutan lindung Bogor.',
        coords: [-6.718, 107.012]
      },
      {
        id: 'lap-05',
        layer: 'laporan',
        kode: 'LAP-2026-095',
        judul: 'Dugaan Kepulan Asap Perkebunan Warga',
        wilayah: 'Kab. Sukabumi',
        luas: '± 0,4 ha',
        sumber: 'Hasil Inspeksi Lapangan',
        provenance: 'Resort Pengelolaan Hutan Sukabumi · 14 Nov 2026',
        status: 'Ditolak',
        kelas_keyakinan: 'NOT_PROVEN',
        pelapor_nama: 'Hendra Wijaya (3202****7741)',
        is_own_report: false,
        assigned_to_petugas: true,
        deskripsi: 'Verifikasi lapangan memastikan lokasi berada di luar kawasan hutan dan tidak ada kerusakan vegetasi lindung.',
        coords: [-6.865, 106.960]
      }
    ],

    konservasi: [
      {
        id: 'kon-01',
        layer: 'konservasi',
        kode: 'KON-01',
        judul: 'Reboisasi 5.000 Bibit Rasamala & Puspa',
        wilayah: 'Kab. Cianjur',
        luas: '45 ha (Target 5.000 bibit)',
        sumber: 'Program Konservasi Terbuka KPH',
        provenance: 'Balai Taman Nasional & Komunitas Hijau · Jadwal 22 Nov 2026',
        status: 'Terbuka untuk Partisipasi',
        is_public_open: true,
        assigned_to_petugas: true,
        kuota: '40 relawan (tersisa 14 slot)',
        survival_rate: '89% (tahap awal)',
        realisasi: '3.250 / 5.000 bibit siap tanam (65%)',
        coords: [-6.775, 107.075]
      },
      {
        id: 'kon-02',
        layer: 'konservasi',
        kode: 'KON-02',
        judul: 'Pemulihan Mata Air & Koridor Hijau',
        wilayah: 'Kab. Cianjur',
        luas: '28 ha',
        sumber: 'Program Konservasi KPH Cianjur',
        provenance: 'KPH Cianjur & Mitra Konservasi · Pembaruan 15 Nov 2026',
        status: 'Berjalan',
        is_public_open: true,
        assigned_to_petugas: true,
        kuota: '25 relawan aktif',
        survival_rate: '92% tingkat hidup tegakan',
        realisasi: 'Realisasi fisik 80% · 2 embung konservasi selesai',
        coords: [-6.832, 107.184]
      },
      {
        id: 'kon-03',
        layer: 'konservasi',
        kode: 'KON-03',
        judul: 'Restorasi Ekosistem Zona Inti Bogor',
        wilayah: 'Kab. Bogor',
        luas: '50 ha',
        sumber: 'Rencana Rehabilitasi Hutan KLHK',
        provenance: 'Direktorat Rehabilitasi Hutan · Program Terjadwal Q1 2027',
        status: 'Direncanakan',
        is_public_open: false,
        assigned_to_petugas: false,
        kuota: 'Internal Petugas & BPKH',
        survival_rate: 'Belum ditanam',
        realisasi: 'Survei petak ukur & persemaian 8.000 bibit',
        coords: [-6.695, 107.022]
      },
      {
        id: 'kon-04',
        layer: 'konservasi',
        kode: 'KON-04',
        judul: 'Penanaman Sabuk Hijau Lereng Sukabumi',
        wilayah: 'Kab. Sukabumi',
        luas: '32 ha',
        sumber: 'Program Konservasi Terbuka CDK',
        provenance: 'Cabang Dinas Kehutanan Wilayah III · 29 Nov 2026',
        status: 'Terbuka untuk Partisipasi',
        is_public_open: true,
        assigned_to_petugas: true,
        kuota: '50 relawan (tersisa 22 slot)',
        survival_rate: '87% target tahun ke-1',
        realisasi: 'Persiapan lubang tanam 70% selesai',
        coords: [-6.835, 106.952]
      }
    ]
  };

  // 3. MAP VARIABLES
  let map;
  let osmTileLayer;
  let satelliteTileLayer;
  let satelliteLabelsLayer;
  let batasGeoJsonLayer;
  let laporanClusterGroup;
  let hotspotClusterGroup;
  let konservasiLayerGroup;
  let userLocationMarker = null;

  const CIANJUR_CENTER = [-6.785, 107.125];
  const CIANJUR_ZOOM = 12;

  // 4. LEAFLET INITIALIZATION
  function initLeafletMap() {
    map = L.map('map', {
      center: CIANJUR_CENTER,
      zoom: CIANJUR_ZOOM,
      zoomControl: false,
      attributionControl: true
    });

    // Custom Z-Index Panes so point markers always render cleanly above area polygons
    map.createPane('batasKawasanPane');
    map.getPane('batasKawasanPane').style.zIndex = 350;

    map.createPane('konservasiPane');
    map.getPane('konservasiPane').style.zIndex = 420;

    map.createPane('titikPanasPane');
    map.getPane('titikPanasPane').style.zIndex = 450;

    map.createPane('laporanPane');
    map.getPane('laporanPane').style.zIndex = 480;

    // Pure OpenStreetMap Tile Layer (tile.openstreetmap.org)
    osmTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors · RimbaWatch GIS',
      maxZoom: 19,
      className: 'osm-minimal-tiles'
    }).addTo(map);

    // Citra Satelit Resolusi Tinggi (Esri World Imagery)
    satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; <a href="https://www.esri.com/" target="_blank" rel="noopener">Esri</a> &mdash; Source: Esri, Maxar, Earthstar Geographics',
      maxZoom: 19
    });

    // Label Batas Administratif untuk Satelit
    satelliteLabelsLayer = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
      maxZoom: 19,
      opacity: 0.85
    });

    // MarkerCluster Groups
    laporanClusterGroup = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 38,
      clusterPane: 'laporanPane'
    });

    hotspotClusterGroup = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 38,
      clusterPane: 'titikPanasPane'
    });

    konservasiLayerGroup = L.layerGroup();

    map.on('moveend zoomend', () => {
      const c = map.getCenter();
      const z = map.getZoom();
      const latDir = c.lat < 0 ? 'S' : 'N';
      const lngDir = c.lng < 0 ? 'W' : 'E';
      const coordsEl = document.getElementById('viewportCoords');
      if (coordsEl) {
        coordsEl.textContent = `${Math.abs(c.lat).toFixed(4)}° ${latDir}, ${Math.abs(c.lng).toFixed(4)}° ${lngDir} · Z${z}`;
      }
    });

    renderMapLayers();
  }

  // 5. RBAC & SPATIAL FILTER ENGINE (PRD Section 4)
  function getAuthorizedDatasets() {
    const role = appState.role;
    const { wilayah, statusLaporan, hotspotFreshness } = appState.filters;
    const matchWilayah = (w) => wilayah === 'all' || w === wilayah;

    // 1. Batas Kawasan: Nonaktif/Arsip visible ONLY to Admin
    const batasFeatures = BATAS_KAWASAN_GEOJSON.features.filter((feat) => {
      const p = feat.properties;
      if (!matchWilayah(p.wilayah)) return false;
      if (role !== 'admin' && p.status !== 'Aktif') return false;
      return true;
    });

    // 2. Laporan Indikasi
    const laporanItems = POINT_FEATURES.laporan.filter((item) => {
      if (!matchWilayah(item.wilayah)) return false;
      if (statusLaporan !== 'all' && item.status !== statusLaporan) return false;
      if (role === 'masyarakat') {
        return item.is_own_report || item.status === 'Terkonfirmasi';
      }
      if (role === 'petugas') {
        return item.assigned_to_petugas;
      }
      return true;
    });

    // 3. Titik Panas
    const hotspotItems = POINT_FEATURES.hotspot.filter((item) => {
      if (!matchWilayah(item.wilayah)) return false;
      if (hotspotFreshness === 'fresh' && item.is_stale) return false;
      if (hotspotFreshness === 'stale' && !item.is_stale) return false;
      return true;
    });

    // 4. Konservasi
    const konservasiItems = POINT_FEATURES.konservasi.filter((item) => {
      if (!matchWilayah(item.wilayah)) return false;
      if (role === 'masyarakat') return item.is_public_open;
      if (role === 'petugas') return item.assigned_to_petugas || item.is_public_open;
      return true;
    });

    return { batasFeatures, laporanItems, hotspotItems, konservasiItems };
  }

  function createCircularPinIcon(bgColor, isSelected, isStale = false) {
    const size = isSelected ? 19 : 15;
    const selClass = isSelected ? 'selected' : '';
    const staleClass = isStale ? 'rw-pin-stale' : '';
    return L.divIcon({
      className: 'bg-transparent border-0',
      html: `<div class="rw-pin ${selClass} ${staleClass}" style="width:${size}px;height:${size}px;background-color:${bgColor};"></div>`,
      iconSize: [size, size],
      iconAnchor: [size / 2, size / 2]
    });
  }

  function renderMapLayers() {
    if (batasGeoJsonLayer && map.hasLayer(batasGeoJsonLayer)) {
      map.removeLayer(batasGeoJsonLayer);
    }
    laporanClusterGroup.clearLayers();
    hotspotClusterGroup.clearLayers();
    konservasiLayerGroup.clearLayers();

    if (map.hasLayer(laporanClusterGroup)) map.removeLayer(laporanClusterGroup);
    if (map.hasLayer(hotspotClusterGroup)) map.removeLayer(hotspotClusterGroup);
    if (map.hasLayer(konservasiLayerGroup)) map.removeLayer(konservasiLayerGroup);

    const { batasFeatures, laporanItems, hotspotItems, konservasiItems } = getAuthorizedDatasets();

    // Update counts in Layer Peta card
    const cBatas = document.getElementById('count-batas');
    const cLaporan = document.getElementById('count-laporan');
    const cHotspot = document.getElementById('count-hotspot');
    const cKonservasi = document.getElementById('count-konservasi');

    if (cBatas) cBatas.textContent = batasFeatures.length;
    if (cLaporan) cLaporan.textContent = laporanItems.length;
    if (cHotspot) cHotspot.textContent = hotspotItems.length;
    if (cKonservasi) cKonservasi.textContent = konservasiItems.length;

    // Ensure selectedFeatureId remains authorized
    const allAuthorizedObjects = [
      ...batasFeatures.map((f) => f.properties),
      ...laporanItems,
      ...hotspotItems,
      ...konservasiItems
    ];
    let selectedObj = allAuthorizedObjects.find((obj) => obj.id === appState.selectedFeatureId);
    if (!selectedObj && allAuthorizedObjects.length > 0) {
      selectedObj = allAuthorizedObjects[0];
      appState.selectedFeatureId = selectedObj.id;
    }

    // Layer 1: Batas Kawasan
    if (appState.layers.batas && batasFeatures.length > 0) {
      batasGeoJsonLayer = L.geoJSON({ type: 'FeatureCollection', features: batasFeatures }, {
        pane: 'batasKawasanPane',
        style: (feature) => {
          const props = feature.properties;
          const isSelected = props.id === appState.selectedFeatureId;
          const isInactive = props.status === 'Nonaktif';
          return {
            color: isInactive ? '#8C6D1F' : '#4F7942',
            weight: isSelected ? 2.8 : 2,
            dashArray: isInactive ? '6, 5' : null,
            fillColor: isInactive ? '#E2DFD5' : '#B6D7A8',
            fillOpacity: isSelected ? 0.78 : 0.65
          };
        },
        onEachFeature: (feature, layer) => {
          const p = feature.properties;
          layer.on('click', (e) => {
            L.DomEvent.stopPropagation(e);
            selectFeature(p.id);
          });
          layer.bindTooltip(`${p.nama} (${p.luas})`, {
            sticky: true,
            className: 'text-xs font-medium rounded-lg px-2 py-1 shadow-sm border border-gray-200'
          });
        }
      }).addTo(map);
    }

    // Layer 4: Konservasi
    if (appState.layers.konservasi) {
      konservasiItems.forEach((item) => {
        const isSelected = item.id === appState.selectedFeatureId;
        const pinColor = item.status === 'Berjalan' ? '#3B6EB5' : '#3E7B42';
        const marker = L.marker(item.coords, {
          pane: 'konservasiPane',
          icon: createCircularPinIcon(pinColor, isSelected, false)
        });
        marker.on('click', (e) => {
          L.DomEvent.stopPropagation(e);
          selectFeature(item.id);
        });
        marker.bindTooltip(`${item.kode} · ${item.judul}`, {
          direction: 'top',
          offset: [0, -8],
          className: 'text-xs font-medium rounded-lg px-2 py-1 shadow-sm border border-gray-200'
        });
        konservasiLayerGroup.addLayer(marker);
      });
      konservasiLayerGroup.addTo(map);
    }

    // Layer 3: Titik Panas
    if (appState.layers.hotspot) {
      hotspotItems.forEach((item) => {
        const isSelected = item.id === appState.selectedFeatureId;
        const marker = L.marker(item.coords, {
          pane: 'titikPanasPane',
          icon: createCircularPinIcon('#E07A22', isSelected, item.is_stale)
        });
        marker.on('click', (e) => {
          L.DomEvent.stopPropagation(e);
          selectFeature(item.id);
        });
        marker.bindTooltip(`${item.kode} · ${item.judul}`, {
          direction: 'top',
          offset: [0, -8],
          className: 'text-xs font-medium rounded-lg px-2 py-1 shadow-sm border border-gray-200'
        });
        hotspotClusterGroup.addLayer(marker);
      });
      map.addLayer(hotspotClusterGroup);
    }

    // Layer 2: Laporan Indikasi
    if (appState.layers.laporan) {
      laporanItems.forEach((item) => {
        const isSelected = item.id === appState.selectedFeatureId;
        const marker = L.marker(item.coords, {
          pane: 'laporanPane',
          icon: createCircularPinIcon('#C83E3B', isSelected, false)
        });
        marker.on('click', (e) => {
          L.DomEvent.stopPropagation(e);
          selectFeature(item.id);
        });
        marker.bindTooltip(`${item.kode} · ${item.judul}`, {
          direction: 'top',
          offset: [0, -8],
          className: 'text-xs font-medium rounded-lg px-2 py-1 shadow-sm border border-gray-200'
        });
        laporanClusterGroup.addLayer(marker);
      });
      map.addLayer(laporanClusterGroup);
    }

    renderSelectedFeatureCard(selectedObj);
    renderDynamicLegend();
    updateActiveLayerCounter();
  }

  // 6. RENDER "FITUR TERPILIH" INSPECTOR CARD & CONTEXTUAL CTA
  function selectFeature(id) {
    appState.selectedFeatureId = id;
    renderMapLayers();
  }

  function renderSelectedFeatureCard(feature) {
    const container = document.getElementById('featureInspectorContent');
    const badgeEl = document.getElementById('selectedFeatureTypeBadge');

    if (!container || !badgeEl) return;

    if (!feature) {
      badgeEl.textContent = 'Kosong';
      container.innerHTML = `<p class="text-xs text-gray-500 py-3">Tidak ada fitur aktif untuk filter atau peran Anda saat ini.</p>`;
      return;
    }

    const role = appState.role;
    const layerLabels = {
      batas: 'Batas Kawasan',
      laporan: 'Laporan Indikasi',
      hotspot: 'Titik Panas',
      konservasi: 'Konservasi'
    };
    badgeEl.textContent = layerLabels[feature.layer] || 'Fitur';

    let statusStyle = 'text-gray-900 font-bold';
    if (['Aktif', 'Terkonfirmasi', 'Terbuka untuk Partisipasi'].includes(feature.status)) {
      statusStyle = 'text-forest-600 font-bold';
    } else if (feature.status === 'Nonaktif') {
      statusStyle = 'text-[#8C6D1F] font-bold';
    } else if (['Indikasi Awal', 'Dalam Inspeksi', 'Perlu Perbaikan'].includes(feature.status)) {
      statusStyle = 'text-amber-700 font-bold';
    } else if (feature.status === 'Ditolak') {
      statusStyle = 'text-red-700 font-bold';
    }

    let html = `
      <div class="pb-2.5 border-b border-gray-100">
        <div class="text-xs font-bold text-gray-900 leading-snug">${feature.nama || feature.judul}</div>
        ${feature.kode ? `<div class="text-[11px] font-mono text-gray-400 mt-0.5">${feature.kode}</div>` : ''}
      </div>

      <div class="space-y-2.5">
        <div>
          <div class="text-[11px] text-gray-400 font-medium">Wilayah</div>
          <div class="text-[13px] font-bold text-gray-900 mt-0.5">${feature.wilayah}</div>
        </div>

        <div>
          <div class="text-[11px] text-gray-400 font-medium">Luas estimasi</div>
          <div class="text-[13px] font-bold text-gray-900 mt-0.5">${feature.luas}</div>
        </div>

        <div>
          <div class="text-[11px] text-gray-400 font-medium">Sumber</div>
          <div class="text-[13px] font-bold text-gray-900 mt-0.5">${feature.sumber}</div>
          <div class="text-[11px] text-gray-500 mt-0.5">${feature.provenance}</div>
        </div>

        <div>
          <div class="text-[11px] text-gray-400 font-medium">Status</div>
          <div class="text-[13px] ${statusStyle} mt-0.5">${feature.status}</div>
        </div>
      </div>
    `;

    // Stale Warning Banner if Hotspot > 24h
    if (feature.layer === 'hotspot') {
      if (feature.is_stale) {
        html += `
          <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-900">
            <div class="font-bold flex items-center gap-1.5">
              <i data-lucide="clock-alert" class="w-3.5 h-3.5 text-amber-700 shrink-0"></i>
              <span>Data Usang (&gt; 24 jam)</span>
            </div>
            <p class="mt-1 text-amber-800/90 leading-relaxed">${feature.freshness_text}. Hanya ditampilkan sebagai referensi anomali suhu historis.</p>
          </div>
        `;
      } else {
        html += `
          <div class="p-2 rounded-xl bg-emerald-50/80 border border-emerald-200 text-[11px] text-forest-800 font-medium">
            &#10003; ${feature.freshness_text} · Kepercayaan: ${feature.confidence}
          </div>
        `;
      }
    }

    // Role-Permitted Technical / Provenance Attributes (PRD Section 4)
    let extraAttrs = '';
    if (feature.layer === 'batas') {
      if (role === 'admin') {
        extraAttrs += `
          <div class="flex justify-between"><span>Nomor Versi:</span><span class="font-mono font-semibold text-gray-800">${feature.nomor_versi}</span></div>
          <div class="flex justify-between"><span>Status Validasi:</span><span class="font-semibold text-forest-700">${feature.status_validasi}</span></div>
        `;
      }
      if (role !== 'masyarakat') {
        extraAttrs += `<p class="text-gray-600 mt-1"><strong class="text-gray-700">Catatan Teknis:</strong> ${feature.catatan_teknis}</p>`;
      }
    } else if (feature.layer === 'hotspot') {
      if (role !== 'masyarakat') {
        extraAttrs += `
          <div class="flex justify-between"><span>Satelit / Suhu Kecerahan:</span><span class="font-semibold text-gray-800">${feature.satellite} · ${feature.brightness}</span></div>
        `;
      }
      if (role === 'admin') {
        extraAttrs += `
          <div class="flex justify-between"><span>Sumber ID:</span><span class="font-mono font-semibold text-gray-800">${feature.sumber_id}</span></div>
          <div class="flex justify-between"><span>Waktu Impor:</span><span class="text-gray-700">${feature.waktu_impor}</span></div>
        `;
      }
    } else if (feature.layer === 'laporan') {
      if (role === 'masyarakat') {
        const reporterLabel = feature.is_own_report ? (cfg.userName || 'Anda (Laporan Pribadi)') : 'Disamarkan (Kebijakan Privasi NF-03)';
        extraAttrs += `
          <div class="flex justify-between"><span>Pelapor:</span><span class="font-semibold text-gray-700">${reporterLabel}</span></div>
          <p class="text-gray-600 mt-1">${feature.deskripsi}</p>
        `;
      } else {
        extraAttrs += `
          <div class="flex justify-between"><span>Kelas Keyakinan:</span><span class="font-mono font-semibold text-forest-800">${feature.kelas_keyakinan}</span></div>
          <div class="flex justify-between"><span>Pelapor:</span><span class="font-semibold text-gray-800">${feature.pelapor_nama}</span></div>
          <p class="text-gray-600 mt-1">${feature.deskripsi}</p>
        `;
      }
    } else if (feature.layer === 'konservasi') {
      extraAttrs += `<div class="flex justify-between"><span>Kuota:</span><span class="font-semibold text-gray-800">${feature.kuota}</span></div>`;
      if (role !== 'masyarakat') {
        extraAttrs += `
          <div class="flex justify-between"><span>Kelangsungan Hidup:</span><span class="font-semibold text-forest-700">${feature.survival_rate}</span></div>
          <p class="text-gray-600 mt-1"><strong class="text-gray-700">Capaian:</strong> ${feature.realisasi}</p>
        `;
      }
    }

    if (extraAttrs) {
      html += `
        <div class="pt-2.5 border-t border-gray-100 space-y-1.5 text-[11px] text-gray-500">
          <div class="font-bold uppercase tracking-wider text-[10px] text-gray-400">Atribut Akses (${ROLE_META[role]?.initials || 'RBAC'})</div>
          ${extraAttrs}
        </div>
      `;
    }

    // Contextual Action Button (CTA)
    const cta = getContextualCTA(role, feature);
    if (cta) {
      html += `
        <div class="pt-2">
          <button type="button" onclick="RW_GIS.openContextualActionModal('${feature.id}')" class="w-full py-2 px-3 rounded-xl bg-forest-800 hover:bg-forest-900 text-white text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5">
            <span>${cta.label}</span>
            <span>&rarr;</span>
          </button>
        </div>
      `;
    }

    container.innerHTML = html;
    if (window.lucide) window.lucide.createIcons();
  }

  function getContextualCTA(role, feature) {
    if (role === 'admin') {
      return { label: 'Kelola Data Kawasan' };
    }
    if (role === 'pengawas') {
      if (feature.layer === 'laporan') return { label: 'Tinjau & Triase Laporan' };
      return { label: 'Evaluasi Area Prioritas' };
    }
    if (role === 'petugas') {
      if (feature.layer === 'laporan') return { label: 'Catat Hasil Inspeksi' };
      if (feature.layer === 'konservasi') return { label: 'Catat Realisasi Kegiatan' };
      return { label: 'Rencanakan Navigasi Lapangan' };
    }
    if (role === 'masyarakat') {
      if (feature.layer === 'konservasi') return { label: 'Daftar Kegiatan Konservasi' };
      if (feature.layer === 'laporan' && feature.is_own_report) return { label: 'Lihat Detail / Perbaiki Laporan' };
      return null;
    }
    return null;
  }

  // 7. DYNAMIC LEGEND
  function renderDynamicLegend() {
    const el = document.getElementById('dynamicLegendList');
    if (!el) return;
    const items = [];

    if (appState.layers.batas) {
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="w-3.5 h-3.5 rounded-[4px] bg-[#B6D7A8] border border-[#4F7942] shrink-0"></span>
          <span>Batas Kawasan Aktif</span>
        </div>
      `);
      if (appState.role === 'admin') {
        items.push(`
          <div class="flex items-center gap-2.5">
            <span class="w-3.5 h-3.5 rounded-[4px] bg-[#E2DFD5] border border-dashed border-[#8C6D1F] shrink-0"></span>
            <span>Batas Kawasan Nonaktif (Arsip)</span>
          </div>
        `);
      }
    }

    if (appState.layers.laporan) {
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="w-3.5 h-3.5 rounded-full bg-[#C83E3B] border-2 border-white shadow-xs shrink-0"></span>
          <span>Laporan Indikasi Kerusakan</span>
        </div>
      `);
    }

    if (appState.layers.hotspot) {
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="w-3.5 h-3.5 rounded-full bg-[#E07A22] border-2 border-white shadow-xs shrink-0"></span>
          <span>Titik Panas — Segar (&lt; 24 jam)</span>
        </div>
      `);
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="relative w-3.5 h-3.5 rounded-full bg-[#E07A22] border-2 border-white ring-1 ring-amber-500 shadow-xs shrink-0">
            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-200 border border-amber-800"></span>
          </span>
          <span>Titik Panas — Data Usang (&gt; 24 jam)</span>
        </div>
      `);
    }

    if (appState.layers.konservasi) {
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="w-3.5 h-3.5 rounded-full bg-[#3E7B42] border-2 border-white shadow-xs shrink-0"></span>
          <span>Konservasi (Terbuka / Reboisasi)</span>
        </div>
      `);
      items.push(`
        <div class="flex items-center gap-2.5">
          <span class="w-3.5 h-3.5 rounded-full bg-[#3B6EB5] border-2 border-white shadow-xs shrink-0"></span>
          <span>Konservasi &amp; Pemulihan Berjalan</span>
        </div>
      `);
    }

    el.innerHTML = items.length > 0
      ? items.join('')
      : `<div class="text-[11px] text-gray-400 italic">Semua layer dinonaktifkan. Centang layer di atas untuk menampilkan simbol legenda.</div>`;
  }

  function updateActiveLayerCounter() {
    const counterEl = document.getElementById('activeLayerCount');
    if (!counterEl) return;
    const count = Object.values(appState.layers).filter(Boolean).length;
    counterEl.textContent = `${count}/4 aktif`;
  }

  // 8. CONTROLS & EVENT HANDLERS
  function toggleMapLayer(layerKey, isChecked) {
    appState.layers[layerKey] = isChecked;
    renderMapLayers();
    showToast(`Layer "${layerKey.toUpperCase()}" ${isChecked ? 'diaktifkan' : 'dinonaktifkan'}.`);
  }

  function setBasemap(styleKey) {
    appState.osmStyle = styleKey;

    const btnSat = document.getElementById('bm-satelit');
    const btnMin = document.getElementById('bm-minimalis');
    const btnStd = document.getElementById('bm-standar');

    const sideBtnSat = document.getElementById('side-bm-satelit');
    const sideBtnMin = document.getElementById('side-bm-minimalis');
    const sideBtnStd = document.getElementById('side-bm-standar');

    const activeFloatingClass = 'px-2.5 py-1 rounded-lg bg-rimba-900 text-white transition flex items-center gap-1.5';
    const inactiveFloatingClass = 'px-2.5 py-1 rounded-lg text-ink-500 hover:bg-rimba-50 transition flex items-center gap-1.5';

    const activeSideClass = 'py-2 px-1 rounded-xl bg-rimba-900 text-white transition flex flex-col items-center justify-center gap-1 text-center';
    const inactiveSideClass = 'py-2 px-1 rounded-xl border border-line hover:bg-rimba-50 text-ink-600 transition flex flex-col items-center justify-center gap-1 text-center';

    const updateButtonClasses = (sat, min, std) => {
      if (btnSat) btnSat.className = sat ? activeFloatingClass : inactiveFloatingClass;
      if (btnMin) btnMin.className = min ? activeFloatingClass : inactiveFloatingClass;
      if (btnStd) btnStd.className = std ? activeFloatingClass : inactiveFloatingClass;

      if (sideBtnSat) sideBtnSat.className = sat ? activeSideClass : inactiveSideClass;
      if (sideBtnMin) sideBtnMin.className = min ? activeSideClass : inactiveSideClass;
      if (sideBtnStd) sideBtnStd.className = std ? activeSideClass : inactiveSideClass;

      if (window.lucide) window.lucide.createIcons();
    };

    if (styleKey === 'satelit') {
      if (map.hasLayer(osmTileLayer)) map.removeLayer(osmTileLayer);
      if (!map.hasLayer(satelliteTileLayer)) satelliteTileLayer.addTo(map);
      if (!map.hasLayer(satelliteLabelsLayer)) satelliteLabelsLayer.addTo(map);

      updateButtonClasses(true, false, false);
      showToast('Peta dasar diubah ke Citra Satelit resolusi tinggi.');
    } else {
      if (map.hasLayer(satelliteTileLayer)) map.removeLayer(satelliteTileLayer);
      if (map.hasLayer(satelliteLabelsLayer)) map.removeLayer(satelliteLabelsLayer);
      if (!map.hasLayer(osmTileLayer)) osmTileLayer.addTo(map);

      const container = osmTileLayer.getContainer();
      if (styleKey === 'minimalis') {
        if (container) container.classList.add('osm-minimal-tiles');
        updateButtonClasses(false, true, false);
        showToast('Peta dasar diubah ke OpenStreetMap Minimalis.');
      } else {
        if (container) container.classList.remove('osm-minimal-tiles');
        updateButtonClasses(false, false, true);
        showToast('Peta dasar diubah ke OpenStreetMap Standar.');
      }
    }
  }

  function setOsmStyle(styleKey) {
    setBasemap(styleKey);
  }

  function setQuickRegionFilter(wilayah) {
    appState.filters.wilayah = wilayah;

    const pills = {
      'all': 'reg-all',
      'Kab. Cianjur': 'reg-cianjur',
      'Kab. Bogor': 'reg-bogor',
      'Kab. Sukabumi': 'reg-sukabumi'
    };
    Object.entries(pills).forEach(([key, elId]) => {
      const el = document.getElementById(elId);
      if (!el) return;
      if (key === wilayah) {
        el.className = 'px-2.5 py-1 rounded-full bg-forest-900 text-white font-semibold transition';
      } else {
        el.className = 'px-2.5 py-1 rounded-full bg-white/85 hover:bg-white text-forest-900 border border-[#D7E6CF] font-semibold transition';
      }
    });

    if (wilayah === 'Kab. Bogor') {
      map.flyTo([-6.708, 107.008], 12, { duration: 0.6 });
    } else if (wilayah === 'Kab. Sukabumi') {
      map.flyTo([-6.850, 106.968], 12, { duration: 0.6 });
    } else {
      map.flyTo(CIANJUR_CENTER, CIANJUR_ZOOM, { duration: 0.6 });
    }

    renderMapLayers();
  }

  function resetMapExtent() {
    setQuickRegionFilter('all');
    showToast('Tampilan peta direset ke wilayah Kab. Cianjur.');
  }

  function geolocateUser() {
    if (!navigator.geolocation) {
      simulateDeviceLocation();
      return;
    }
    showToast('Mendeteksi koordinat GPS perangkat Anda...');
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        placeUserMarker(pos.coords.latitude, pos.coords.longitude, 'Lokasi GPS Perangkat Anda');
      },
      () => {
        simulateDeviceLocation();
      },
      { timeout: 5000 }
    );
  }

  function simulateDeviceLocation() {
    const simLat = -6.7885;
    const simLng = 107.1215;
    placeUserMarker(simLat, simLng, 'Lokasi Terdeteksi (Kab. Cianjur)');
  }

  function placeUserMarker(lat, lng, label) {
    if (userLocationMarker) map.removeLayer(userLocationMarker);
    const pulseIcon = L.divIcon({
      className: 'bg-transparent border-0',
      html: '<div class="user-loc-pulse"></div>',
      iconSize: [14, 14],
      iconAnchor: [7, 7]
    });
    userLocationMarker = L.marker([lat, lng], { icon: pulseIcon })
      .addTo(map)
      .bindPopup(`<span class="text-xs font-semibold">${label}</span>`)
      .openPopup();
    map.flyTo([lat, lng], 13, { duration: 0.6 });
    showToast(label);
  }

  function toggleSidebar(open) {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar) return;
    if (open) {
      sidebar.classList.remove('-translate-x-full');
      if (backdrop) backdrop.classList.remove('hidden');
    } else {
      sidebar.classList.add('-translate-x-full');
      if (backdrop) backdrop.classList.add('hidden');
    }
    setTimeout(() => {
      if (map) map.invalidateSize();
    }, 220);
  }

  // 9. MODALS: AKSI LANJUTAN, DISCLAIMER PRD, CONTEXTUAL ACTION
  function openModal(subtitle, title, bodyHtml, footerHtml = null) {
    const subEl = document.getElementById('modalSubtitle');
    const titleEl = document.getElementById('modalTitle');
    const bodyEl = document.getElementById('modalBody');
    const footerEl = document.getElementById('modalFooter');
    const overlay = document.getElementById('modalOverlay');

    if (subEl) subEl.textContent = subtitle;
    if (titleEl) titleEl.textContent = title;
    if (bodyEl) bodyEl.innerHTML = bodyHtml;
    if (footerEl) {
      footerEl.innerHTML = footerHtml || `
        <button type="button" onclick="RW_GIS.closeModal()" class="px-4 py-2 rounded-xl bg-forest-800 text-white text-xs font-semibold hover:bg-forest-900 transition">Tutup</button>
      `;
    }
    if (overlay) {
      overlay.classList.remove('hidden');
      overlay.classList.add('flex');
    }
    if (window.lucide) window.lucide.createIcons();
  }

  function closeModal() {
    const overlay = document.getElementById('modalOverlay');
    if (!overlay) return;
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
  }

  function openAdvancedModal(tab = 'filter') {
    appState.activeModalTab = tab;
    renderAdvancedModalContent();
  }

  function switchAdvancedTab(tab) {
    appState.activeModalTab = tab;
    renderAdvancedModalContent();
  }

  function renderAdvancedModalContent() {
    const tab = appState.activeModalTab;
    const role = appState.role;
    const roleMeta = ROLE_META[role] || ROLE_META.admin;
    const { batasFeatures, laporanItems, hotspotItems, konservasiItems } = getAuthorizedDatasets();

    const tabHeader = `
      <div class="flex gap-1.5 p-1 bg-gray-100 rounded-xl mb-4">
        <button type="button" onclick="RW_GIS.switchAdvancedTab('filter')" class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition ${tab === 'filter' ? 'bg-white text-forest-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900'}">
          Filter Spasial (PBI-05)
        </button>
        <button type="button" onclick="RW_GIS.switchAdvancedTab('rbac')" class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition ${tab === 'rbac' ? 'bg-white text-forest-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900'}">
          Inspeksi API / GeoJSON RBAC (NF-03)
        </button>
      </div>
    `;

    let tabBody = '';
    if (tab === 'filter') {
      tabBody = `
        <div class="space-y-3.5">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Wilayah Administratif</label>
            <select id="modalFilterWilayah" onchange="RW_GIS.applyModalFilters()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800">
              <option value="all" ${appState.filters.wilayah === 'all' ? 'selected' : ''}>Semua Wilayah (Cianjur, Bogor, Sukabumi)</option>
              <option value="Kab. Cianjur" ${appState.filters.wilayah === 'Kab. Cianjur' ? 'selected' : ''}>Kab. Cianjur</option>
              <option value="Kab. Bogor" ${appState.filters.wilayah === 'Kab. Bogor' ? 'selected' : ''}>Kab. Bogor</option>
              <option value="Kab. Sukabumi" ${appState.filters.wilayah === 'Kab. Sukabumi' ? 'selected' : ''}>Kab. Sukabumi</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Status Laporan Indikasi</label>
            <select id="modalFilterStatusLaporan" onchange="RW_GIS.applyModalFilters()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800">
              <option value="all" ${appState.filters.statusLaporan === 'all' ? 'selected' : ''}>Semua Status Diizinkan</option>
              <option value="Baru" ${appState.filters.statusLaporan === 'Baru' ? 'selected' : ''}>Baru</option>
              <option value="Perlu Perbaikan" ${appState.filters.statusLaporan === 'Perlu Perbaikan' ? 'selected' : ''}>Perlu Perbaikan</option>
              <option value="Dalam Inspeksi" ${appState.filters.statusLaporan === 'Dalam Inspeksi' ? 'selected' : ''}>Dalam Inspeksi</option>
              <option value="Terkonfirmasi" ${appState.filters.statusLaporan === 'Terkonfirmasi' ? 'selected' : ''}>Terkonfirmasi</option>
              <option value="Ditolak" ${appState.filters.statusLaporan === 'Ditolak' ? 'selected' : ''}>Ditolak / Tidak Terbukti</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Kesegaran Data Titik Panas (Hotspot)</label>
            <select id="modalFilterHotspot" onchange="RW_GIS.applyModalFilters()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800">
              <option value="all" ${appState.filters.hotspotFreshness === 'all' ? 'selected' : ''}>Semua (Data Segar &amp; Data Usang)</option>
              <option value="fresh" ${appState.filters.hotspotFreshness === 'fresh' ? 'selected' : ''}>Hanya Segar (&lt; 24 jam)</option>
              <option value="stale" ${appState.filters.hotspotFreshness === 'stale' ? 'selected' : ''}>Hanya Data Usang (&gt; 24 jam)</option>
            </select>
          </div>
        </div>
      `;
    } else {
      const simulatedPayload = {
        endpoint: 'GET /api/v1/gis/features?bbox=106.92,-6.92,107.22,-6.68',
        basemap_provider: 'OpenStreetMap (tile.openstreetmap.org)',
        authenticated_role: role,
        user_identity: cfg.userName || 'Tervalidasi',
        rbac_policy_applied: roleMeta.description,
        nf03_reporter_privacy_guard: role === 'masyarakat'
          ? 'AKTIF: Nama & kontak pelapor lain disamarkan otomatis; koordinat sensitif dibatasi'
          : (role === 'petugas' ? 'PENUGASAN_LAPANGAN_AKTIF_SAJA' : 'AKSES_OPERASIONAL_LENGKAP'),
        feature_counts_returned: {
          batas_kawasan: batasFeatures.map((f) => `${f.properties.nama} [${f.properties.status}]`),
          laporan_indikasi: laporanItems.length,
          titik_panas: hotspotItems.length,
          konservasi: konservasiItems.length
        },
        laporan_sample: laporanItems.map((l) => ({
          kode: l.kode,
          status: l.status,
          pelapor: role === 'masyarakat' && !l.is_own_report ? '[DISAMARKAN_PRIVASI_NF03]' : l.pelapor_nama
        }))
      };

      tabBody = `
        <div class="p-3 rounded-xl bg-[#EEF5EA] border border-[#D7E6CF] text-forest-900 text-xs mb-3">
          <strong>Peran Anda (${roleMeta.label}):</strong> ${roleMeta.description}
        </div>
        <pre class="bg-forest-950 text-limepill p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto leading-relaxed">${JSON.stringify(simulatedPayload, null, 2)}</pre>
      `;
    }

    openModal(
      'AKSI LANJUTAN · INTEGRASI PBI-05 & KEAMANAN ENDPOINT RBAC',
      'Filter Spasial & Inspeksi API GeoJSON',
      tabHeader + tabBody,
      `
        <button type="button" onclick="RW_GIS.resetAllFilters()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">Reset Filter</button>
        <button type="button" onclick="RW_GIS.closeModal()" class="px-4 py-2 rounded-xl bg-forest-800 text-white text-xs font-semibold hover:bg-forest-900">Terapkan &amp; Tutup</button>
      `
    );
  }

  function applyModalFilters() {
    const wEl = document.getElementById('modalFilterWilayah');
    const sEl = document.getElementById('modalFilterStatusLaporan');
    const hEl = document.getElementById('modalFilterHotspot');
    if (wEl) setQuickRegionFilter(wEl.value);
    if (sEl) appState.filters.statusLaporan = sEl.value;
    if (hEl) appState.filters.hotspotFreshness = hEl.value;
    renderMapLayers();
  }

  function resetAllFilters() {
    appState.filters = { wilayah: 'all', statusLaporan: 'all', hotspotFreshness: 'all' };
    setQuickRegionFilter('all');
    closeModal();
    showToast('Seluruh filter spasial telah direset ke kondisi awal.');
  }

  function openDisclaimerModal() {
    openModal(
      'BATASAN SISTEM & TRANSPARANSI DATA (PRD BAGIAN 2)',
      'Indikasi ≠ Konfirmasi Lapangan',
      `
        <div class="p-3 rounded-xl bg-creamwarn border border-[#F3E2BE] text-[#8A6116]">
          <strong>Bukan Deteksi Otomatis:</strong> Peta interaktif RimbaWatch memvisualisasikan data geospasial resmi yang diimpor dari sumber terverifikasi maupun dicatat oleh pengguna; sistem tidak menjalankan deteksi citra satelit atau kecerdasan buatan secara otomatis.
        </div>
        <div class="space-y-3 text-gray-600">
          <div>
            <h4 class="font-bold text-gray-900">1. Disclaimer Titik Panas (Hotspot)</h4>
            <p class="mt-0.5">Titik panas ditampilkan murni sebagai <strong>indikasi awal anomali suhu permukaan</strong>, bukan konfirmasi kejadian kebakaran atau luas area terbakar. Data yang melewati interval 24 jam ditandai sebagai <em>Data Usang</em>.</p>
          </div>
          <div>
            <h4 class="font-bold text-gray-900">2. Disclaimer Batas Kawasan</h4>
            <p class="mt-0.5">Poligon batas kawasan berfungsi sebagai <strong>referensi spasial</strong> sesuai dataset penetapan resmi dan bukan merupakan bukti legalitas atau kepemilikan hak atas tanah perorangan.</p>
          </div>
          <div>
            <h4 class="font-bold text-gray-900">3. Privasi Pelapor &amp; Status Verifikasi (NF-03)</h4>
            <p class="mt-0.5">Laporan masyarakat yang belum diverifikasi tidak disajikan sebagai kejadian yang telah terbukti, dan identitas pribadi pelapor wajib disembunyikan dari akses publik demi keamanan pelapor.</p>
          </div>
        </div>
      `
    );
  }

  function openContextualActionModal(featureId) {
    const { batasFeatures, laporanItems, hotspotItems, konservasiItems } = getAuthorizedDatasets();
    const all = [
      ...batasFeatures.map((f) => f.properties),
      ...laporanItems,
      ...hotspotItems,
      ...konservasiItems
    ];
    const f = all.find((item) => item.id === featureId);
    if (!f) return;

    const cta = getContextualCTA(appState.role, f);
    const roleLabel = ROLE_META[appState.role]?.label || 'Petugas';

    openModal(
      `AKSI KONTEKSTUAL · ${roleLabel.toUpperCase()}`,
      cta ? cta.label : 'Tindak Lanjut Fitur',
      `
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
          <div class="text-xs font-bold text-gray-900">${f.nama || f.judul}</div>
          <div class="text-[11px] text-gray-500">Wilayah: ${f.wilayah} · Status: <strong>${f.status}</strong></div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tindak Lanjut / Konfirmasi Lapangan</label>
          <textarea rows="3" class="w-full rounded-xl border border-gray-200 p-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-forest-600/30" placeholder="Tuliskan catatan verifikasi, hasil inspeksi lapangan, atau konfirmasi partisipasi kegiatan..."></textarea>
        </div>
      `,
      `
        <button type="button" onclick="RW_GIS.closeModal()" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
        <button type="button" onclick="RW_GIS.closeModal(); RW_GIS.showToast('Data tindak lanjut untuk ${(f.nama || f.judul).replace(/'/g, '')} berhasil disimpan.');" class="px-4 py-2 rounded-xl bg-forest-800 text-white text-xs font-semibold hover:bg-forest-900">Simpan Tindak Lanjut</button>
      `
    );
  }

  // 10. TOAST NOTIFICATION
  function showToast(message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto bg-forest-900 text-white text-xs font-medium px-4 py-3 rounded-xl shadow-lg border border-white/10 flex items-center gap-2.5 transition-all duration-200';
    toast.innerHTML = `
      <span class="w-2 h-2 rounded-full bg-limepill shrink-0"></span>
      <span>${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(6px)';
      setTimeout(() => toast.remove(), 220);
    }, 3200);
  }

  // EXPORT TO GLOBAL SCOPE
  window.RW_GIS = {
    initLeafletMap,
    renderMapLayers,
    selectFeature,
    toggleMapLayer,
    setBasemap,
    setOsmStyle,
    setQuickRegionFilter,
    resetMapExtent,
    geolocateUser,
    toggleSidebar,
    openModal,
    closeModal,
    openAdvancedModal,
    switchAdvancedTab,
    applyModalFilters,
    resetAllFilters,
    openDisclaimerModal,
    openContextualActionModal,
    showToast,
    getAppState: () => appState
  };

  // Auto-init on window load
  window.addEventListener('load', () => {
    if (window.lucide) window.lucide.createIcons();
    initLeafletMap();
  });

  window.addEventListener('resize', () => {
    if (map) map.invalidateSize();
  });
})();
