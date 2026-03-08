<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-map-location-dot text-indigo-600 mr-2"></i> {{ __('Peta Sebaran Aset (WebGIS)') }}
            </h2>
            <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded-full border border-gray-200 shadow-sm flex items-center">
                <i class="fa-solid fa-server text-green-500 mr-2"></i> PostGIS MVT 
            </div>
        </div>
    </x-slot>

    {{-- ========================================================= --}}
    {{-- LOGIKA HAK AKSES (Diletakkan di View agar tidak Error)    --}}
    {{-- ========================================================= --}}
    @php
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;
        
        $bisaKelolaLayer = $isAdmin || in_array('Kelola Layer', $aksesMenu);
        $bisaLihatData = $isAdmin || in_array('Data Aset', $aksesMenu);
        $bisaLihatStatistik = $isAdmin || in_array('Statistik', $aksesMenu);
    @endphp

    {{-- KONTANER UTAMA PETA --}}
    <div class="relative w-full bg-gray-200 overflow-hidden" style="height: calc(100vh - 140px); min-height: 600px;">
        
        {{-- LOADING INDICATOR --}}
        <div id="map-loading" class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[2000] bg-white/95 px-6 py-3 rounded-full font-bold shadow-2xl text-gray-700 flex items-center border border-gray-100 backdrop-blur-sm">
            <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-xl mr-3"></i> 
            <span id="loading-text">Memuat Data Spasial...</span>
        </div>

        {{-- ========================================================= --}}
        {{-- SUB MENU TENGAH ATAS (DATA ASET & STATISTIK) --}}
        {{-- ========================================================= --}}
        @if($bisaLihatData || $bisaLihatStatistik)
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-[1000] flex gap-2 bg-white/90 backdrop-blur-md p-1.5 rounded-xl shadow-lg border border-gray-200">
            @if($bisaLihatData)
            <a href="#" class="px-5 py-2 rounded-lg text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition flex items-center">
                <i class="fa-solid fa-table-list text-blue-500 mr-2"></i> Data Aset
            </a>
            @endif
            
            @if($bisaLihatData && $bisaLihatStatistik)
            <div class="w-px bg-gray-300 my-1"></div>
            @endif

            @if($bisaLihatStatistik)
            <a href="#" class="px-5 py-2 rounded-lg text-sm font-bold text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition flex items-center">
                <i class="fa-solid fa-chart-pie text-orange-500 mr-2"></i> Statistik
            </a>
            @endif
        </div>
        @endif

        {{-- ========================================================= --}}
        {{-- PANEL KANAN: ALAT FILTER & MANAJEMEN LAYER --}}
        {{-- ========================================================= --}}
        
        {{-- 1. PANEL FILTER PETA --}}
        <div class="absolute top-4 right-4 z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-[320px] transition-all">
            <h6 class="font-bold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                <i class="fa-solid fa-filter text-indigo-600 mr-2"></i> Filter & Alat
            </h6>
            
            <div class="space-y-3 mb-3">
                <div>
                    <label class="text-[11px] font-bold text-gray-600 mb-1 block uppercase tracking-wider">Pencarian (Nama/NIB)</label>
                    <div class="relative">
                        <input type="text" id="searchMap" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pr-8" placeholder="Ketik kata kunci...">
                        <button onclick="document.getElementById('searchMap').value=''" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div>
                    <label class="text-[11px] font-bold text-gray-600 mb-1 block uppercase tracking-wider">Tipe Hak</label>
                    <select id="filterHak" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 py-1.5">
                        <option value="">Semua Tipe Hak</option>
                        <option value="HM">Hak Milik (HM)</option>
                        <option value="HGB">Hak Guna Bangunan (HGB)</option>
                        <option value="HGU">Hak Guna Usaha (HGU)</option>
                        <option value="HP">Hak Pakai (HP)</option>
                        <option value="WAKAF">Tanah Wakaf</option>
                    </select>
                </div>
            </div>

            {{-- Susunan Tombol Sejajar (Rata) --}}
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button onclick="alert('Fitur filter spesifik sedang disiapkan.')" class="w-full bg-indigo-100 text-indigo-700 text-xs font-bold py-2 rounded-md hover:bg-indigo-200 transition shadow-sm flex items-center justify-center">
                    <i class="fa-solid fa-search mr-1.5"></i> Filter
                </button>
                
                @if($bisaKelolaLayer)
                <button onclick="bukaModal('modalUploadShp')" class="w-full bg-emerald-600 text-white text-xs font-bold py-2 rounded-md hover:bg-emerald-700 transition shadow-sm flex items-center justify-center">
                    <i class="fa-solid fa-cloud-upload-alt mr-1.5"></i> Upload SHP
                </button>
                @endif
            </div>
        </div>

        {{-- 2. PANEL LAYER AKTIF --}}
        <div class="absolute top-[315px] right-4 z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-[320px] transition-all">
            <h6 class="font-bold text-gray-800 mb-2 flex items-center justify-between text-sm border-b pb-2">
                <span><i class="fa-solid fa-layer-group text-indigo-600 mr-2"></i> Layer Aktif</span>
            </h6>
            
            <div id="layerList" class="max-h-[160px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                @forelse($layers as $layer)
                    <div class="flex items-center justify-between p-1.5 hover:bg-gray-50 rounded-md border border-transparent hover:border-gray-100 transition group">
                        <label class="flex items-center text-sm text-gray-700 cursor-pointer flex-1 truncate pr-2">
                            <input type="checkbox" class="layer-toggle mr-2 rounded w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 shadow-sm" 
                                   value="{{ $layer->id }}" data-warna="{{ $layer->warna }}"
                                   data-url="{{ url('/map/tiles/' . $layer->id . '/{z}/{x}/{y}.pbf') }}">
                            <span class="truncate font-medium">{{ $layer->nama_layer }}</span>
                        </label>
                        @if($bisaKelolaLayer)
                            <input type="color" class="layer-color-picker w-6 h-6 rounded cursor-pointer border border-gray-300 p-0 overflow-hidden shrink-0 opacity-70 group-hover:opacity-100 transition" 
                                   value="{{ $layer->warna }}" data-id="{{ $layer->id }}" title="Ubah Warna Layer">
                        @else
                            <div class="w-4 h-4 rounded-full border border-gray-300 shrink-0 shadow-sm" style="background-color: {{ $layer->warna }}"></div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-gray-500 italic py-2 text-center">Belum ada layer SHP di database.</p>
                @endforelse
            </div>

            <div class="border-t border-gray-200 mt-3 pt-3">
                <div class="flex justify-between text-[11px] font-bold text-gray-600 mb-1">
                    <span>Transparansi Layer</span> <span id="opacityVal">60%</span>
                </div>
                <input type="range" id="opacitySlider" min="0.1" max="1" step="0.1" value="0.6" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            </div>

            @if($bisaKelolaLayer)
                <button onclick="bukaModal('modalAddLayer')" class="w-full border-2 border-indigo-200 text-indigo-700 text-xs font-bold py-2 rounded-md hover:bg-indigo-50 hover:border-indigo-300 transition mt-4 flex items-center justify-center">
                    <i class="fa-solid fa-plus mr-1.5"></i> Buat Layer Kosong
                </button>
            @endif
        </div>


        {{-- ========================================================= --}}
        {{-- PANEL KIRI BAWAH: LEGENDA --}}
        {{-- ========================================================= --}}
        <div class="absolute bottom-8 left-[15px] z-[1000] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 w-48 transition-all">
            <h6 class="font-bold text-gray-800 mb-2 border-b pb-1 text-[11px] uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-info-circle text-indigo-600 mr-1.5"></i> Legenda Hak
            </h6>
            <div class="space-y-1.5 text-[11px] text-gray-700 font-medium">
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#28a745] border border-gray-300"></div> Hak Milik (HM)</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#ffc107] border border-gray-300"></div> HGB</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#17a2b8] border border-gray-300"></div> Hak Pakai (HP)</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#fd7e14] border border-gray-300"></div> HGU</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#6f42c1] border border-gray-300"></div> Tanah Wakaf</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#6c757d] border border-gray-300"></div> Tanah Negara</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#3388ff] border border-gray-300"></div> Default Layer</div>
            </div>
        </div>

        {{-- WADAH PETA UTAMA --}}
        <div id="main-map" style="width: 100%; height: 100%; z-index: 10;"></div>
    </div>


    {{-- ========================================================= --}}
    {{-- MODAL - MODAL APLIKASI --}}
    {{-- ========================================================= --}}

    @if($bisaKelolaLayer)
    {{-- 1. Modal Upload SHP --}}
    <div id="modalUploadShp" class="fixed inset-0 z-[3000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-gray-100">
                <div class="bg-indigo-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg"><i class="fa-solid fa-cloud-upload-alt mr-2"></i> Upload File SHP</h3>
                    <button onclick="tutupModal('modalUploadShp')" class="hover:text-indigo-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form action="{{ route('map.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg border border-blue-200 flex items-start">
                        <i class="fa-solid fa-circle-info mt-0.5 mr-2 text-blue-500 text-base"></i>
                        <span>Pastikan Anda mengunggah file <b>.ZIP</b> yang didalamnya berisi lengkap komponen Shapefile (.shp, .shx, .dbf, .prj).</span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Layer (Alias) <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_layer" required class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500" placeholder="Misal: Batas Desa 2026...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File (.ZIP) <span class="text-red-500">*</span></label>
                        <input type="file" name="file_zip" accept=".zip" required class="w-full text-sm border border-gray-300 rounded-md p-1.5 bg-gray-50 cursor-pointer file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Warna Utama Poligon / Garis</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="warna" value="#3388ff" class="w-14 h-10 rounded cursor-pointer border border-gray-300 p-0.5">
                            <span class="text-xs text-gray-500 italic">Bisa diubah kembali nanti.</span>
                        </div>
                    </div>
                    <div class="pt-5 border-t flex justify-end gap-3 mt-6">
                        <button type="button" onclick="tutupModal('modalUploadShp')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition shadow-sm">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition shadow-sm flex items-center">
                            <i class="fa-solid fa-upload mr-2"></i> Proses Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Modal Buat Layer Kosong --}}
    <div id="modalAddLayer" class="fixed inset-0 z-[3000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center text-white">
                    <h3 class="font-bold"><i class="fa-solid fa-layer-group mr-2"></i> Buat Layer Baru</h3>
                    <button onclick="tutupModal('modalAddLayer')" class="hover:text-indigo-200"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Layer</label>
                        <input type="text" id="newLayerName" class="w-full text-sm border-gray-300 rounded-md" placeholder="Misal: Jalan, Sungai...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Warna Default</label>
                        <input type="color" id="newLayerColor" value="#3388ff" class="w-14 h-10 rounded border border-gray-300 p-0.5 cursor-pointer">
                    </div>
                    <div class="pt-4 border-t flex justify-end gap-2 mt-2">
                        <button onclick="tutupModal('modalAddLayer')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200">Batal</button>
                        <button onclick="alert('Fungsi simpan layer kosong sedang dikembangkan.')" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">Simpan Layer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Modal Draw (Gambar Manual) --}}
    <div id="modalDraw" class="fixed inset-0 z-[3000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4 py-10">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="bg-emerald-600 px-4 py-3 flex justify-between items-center text-white">
                    <h3 class="font-bold"><i class="fa-solid fa-pen-to-square mr-2"></i> Simpan Data Bidang (Manual)</h3>
                    <button onclick="batalDraw()" class="hover:text-emerald-200"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                <form id="formDraw" class="p-5 space-y-3">
                    <input type="hidden" id="drawGeometry">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Nama Aset / Bidang <span class="text-red-500">*</span></label>
                        <input type="text" required class="w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Contoh: Tanah Wakaf Masjid...">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Tipe Hak <span class="text-red-500">*</span></label>
                            <select class="w-full mt-1 text-sm border-gray-300 rounded-md">
                                <option value="HM">Hak Milik (HM)</option>
                                <option value="HP">Hak Pakai (HP)</option>
                                <option value="HGB">HGB</option>
                                <option value="HGU">HGU</option>
                                <option value="Wakaf">Wakaf</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Warna Poligon</label>
                            <input type="color" value="#ff0000" class="w-full h-[38px] mt-1 rounded cursor-pointer border border-gray-300 p-0.5">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Kecamatan</label>
                            <input type="text" class="w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Nama Kecamatan">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Desa/Kelurahan</label>
                            <input type="text" class="w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Nama Desa">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Keterangan Penggunaan</label>
                        <textarea rows="2" class="w-full mt-1 text-sm border-gray-300 rounded-md" placeholder="Contoh: Sawah, Kebun..."></textarea>
                    </div>
                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1"><i class="fa-solid fa-file-pdf text-red-500 mr-1"></i> Upload Dokumen (Opsional)</label>
                        <input type="file" accept=".pdf" class="w-full text-xs text-gray-500 border border-gray-300 rounded bg-white p-1">
                    </div>
                    <div class="pt-3 border-t flex justify-end gap-2 mt-4">
                        <button type="button" onclick="batalDraw()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200">Batal</button>
                        <button type="button" onclick="alert('Simpan polygon manual sedang dikembangkan.')" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-bold hover:bg-emerald-700">Simpan Aset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Modifikasi Posisi Kontrol Peta */
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; margin-left: 15px !important; margin-top: 15px !important; }
        .leaflet-control-zoom a { color: #4f46e5 !important; }
        
        .leaflet-control-layers { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; border-radius: 8px !important; margin-bottom: 25px !important; margin-left: 215px !important; }
        
        .leaflet-draw-toolbar { margin-left: 15px !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; }
        .leaflet-control-geocoder { margin-left: 15px !important; margin-top: 15px !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; border: none !important; border-radius: 6px !important; }
    </style>

    <script>
        // Modal Handlers
        function bukaModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

        document.addEventListener('DOMContentLoaded', function () {
            // Tangkap Notifikasi
            @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{!! session('success') !!}' }); @endif
            @if(session('error')) Swal.fire({ icon: 'error', title: 'Gagal!', text: '{!! session('error') !!}' }); @endif

            // INISIASI PETA
            var map = L.map('main-map', { zoomControl: false, maxZoom: 22 }).setView([-7.8200, 112.0118], 13);
            L.control.zoom({ position: 'topleft' }).addTo(map);

            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxNativeZoom: 19, maxZoom: 22 });
            var googleSatLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{ maxNativeZoom: 20, maxZoom: 22 });
            osmLayer.addTo(map);
            L.control.layers({ "Peta Jalan (OSM)": osmLayer, "Satelit (Google)": googleSatLayer }, null, { position: 'bottomleft' }).addTo(map);

            // LOGIKA GEOJSON & RENDERER (DIADOPSI DARI WEB_GIS_KEDIRI)
            var currentOpacity = 0.6;
            
            function getColor(props) {
                if (props.layer_color) return props.layer_color;
                var raw = props.raw_data || {};
                var tipe = (raw.TIPEHAK || raw.TIPE_HAK || '').toString().toUpperCase();
                if (tipe.includes('HM') || tipe.includes('MILIK')) return '#28a745';
                if (tipe.includes('HGB') || tipe.includes('BANGUNAN')) return '#ffc107';
                if (tipe.includes('HGU') || tipe.includes('USAHA')) return '#fd7e14';
                if (tipe.includes('HP') || tipe.includes('PAKAI')) return '#17a2b8';
                if (tipe.includes('WAKAF')) return '#6f42c1';
                return '#3388ff';
            }

            var geoJsonLayer = L.geoJSON(null, {
                style: function(feature) {
                    var col = getColor(feature.properties || {});
                    return { color: '#ffffff', fillColor: col, weight: 1.5, opacity: 1, fillOpacity: currentOpacity };
                },
                pointToLayer: function(feature, latlng) {
                    if (feature.properties.type === 'cluster') {
                        var size = feature.properties.count > 100 ? 40 : 30;
                        var icon = L.divIcon({ className: 'bg-red-500 text-white rounded-full font-bold flex items-center justify-center border-2 border-white shadow-md', html: feature.properties.count, iconSize: [size, size] });
                        return L.marker(latlng, { icon: icon });
                    }
                    return L.marker(latlng);
                },
                onEachFeature: function(feature, layer) {
                    if (feature.properties.type === 'cluster') {
                        layer.bindPopup(`<b>Area Padat</b><br>${feature.properties.count} Aset.<br>Zoom in.`);
                        layer.on('click', function() { map.flyTo(layer.getLatLng(), map.getZoom() + 2); });
                    } else {
                        var p = feature.properties;
                        var raw = p.raw_data || {};
                        var content = `
                            <div class="p-1 min-w-[220px]">
                                <h6 class="text-indigo-700 font-bold border-b border-gray-200 pb-1 mb-2">${p.name || 'Data Aset'}</h6>
                                <table class="w-full text-xs text-gray-700">
                                    <tr><td class="font-semibold py-1 w-1/3">Tipe Hak</td><td>: ${raw.TIPEHAK || raw.TIPE_HAK || '-'}</td></tr>
                                    <tr><td class="font-semibold py-1">Luas</td><td>: ${raw.LUASTERTUL || raw.LUAS || 0} m²</td></tr>
                                    <tr><td class="font-semibold py-1">Lokasi</td><td>: ${raw.KELURAHAN || raw.DESA || '-'}</td></tr>
                                </table>
                                @if($bisaKelolaLayer)
                                <div class="mt-3 flex justify-end gap-1 border-t pt-2">
                                    <button class="bg-red-500 hover:bg-red-600 text-white text-[10px] px-3 py-1 rounded shadow-sm" onclick="deleteAsset(${p.id})"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </div>
                                @endif
                            </div>`;
                        layer.bindPopup(content);
                    }
                }
            }).addTo(map);

            var abortController = null;
            window.loadData = function() {
                var loading = document.getElementById('map-loading');
                if(loading) loading.classList.remove('hidden');
                
                var selectedLayers = [];
                document.querySelectorAll('.layer-toggle:checked').forEach(function(cb) { selectedLayers.push(cb.value); });

                var params = new URLSearchParams({
                    north: map.getBounds().getNorth(), south: map.getBounds().getSouth(),
                    east: map.getBounds().getEast(), west: map.getBounds().getWest(),
                    zoom: map.getZoom(), search: document.getElementById('searchMap').value, hak: document.getElementById('filterHak').value
                });
                selectedLayers.forEach(id => params.append('layers[]', id));

                if (abortController) abortController.abort();
                abortController = new AbortController();

                fetch("{{ route('map.api') }}?" + params.toString(), { signal: abortController.signal })
                    .then(res => res.json())
                    .then(data => {
                        geoJsonLayer.clearLayers();
                        if(data.features && data.features.length > 0) geoJsonLayer.addData(data);
                        if(loading) loading.classList.add('hidden');
                    })
                    .catch(err => { if (err.name !== 'AbortError' && loading) loading.classList.add('hidden'); });
            };

            // Trigger Load Data saat Peta Digeser
            map.on('moveend', loadData); 
            document.querySelectorAll('.layer-toggle').forEach(cb => cb.addEventListener('change', loadData));

            // Slider Opacity
            document.getElementById('opacitySlider').addEventListener('input', function(e) {
                currentOpacity = e.target.value;
                document.getElementById('opacityVal').innerText = Math.round(currentOpacity * 100) + '%';
                geoJsonLayer.eachLayer(function(layer) { if (layer.options && layer.options.fill) layer.setStyle({ fillOpacity: currentOpacity }); });
            });

            // Ganti Warna Layer Ajax
            document.querySelectorAll('.layer-color-picker').forEach(picker => {
                picker.addEventListener('change', function() {
                    let layerId = this.getAttribute('data-id');
                    let newColor = this.value;
                    let csrfToken = document.querySelector('input[name="_token"]').value;

                    fetch(`/map/update-warna/${layerId}`, {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PATCH' },
                        body: JSON.stringify({ warna: newColor })
                    }).then(res => { loadData(); Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({ icon: 'success', title: 'Warna layer diupdate' }); });
                });
            });

            // Global Fungsi Delete Aset
            window.deleteAsset = function(id) {
                Swal.fire({ title: 'Hapus Aset?', text: "Data hilang permanen!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!' }).then((result) => {
                    if (result.isConfirmed) {
                        let csrfToken = document.querySelector('input[name="_token"]').value;
                        fetch(`/map/asset/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                        .then(res => res.json()).then(data => { Swal.fire('Terhapus!', data.message, 'success'); loadData(); });
                    }
                });
            };

            // Init Render Pertama Kali
            loadData();
        });
    </script>
    @endpush
</x-app-layout>