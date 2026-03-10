<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-map-location-dot text-indigo-600 mr-2"></i> {{ __('Peta Sebaran Aset (WebGIS)') }}
            </h2>
            <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded-full border border-gray-200 shadow-sm flex items-center">
                <i class="fa-solid fa-bolt text-green-500 mr-2"></i> Canvas GeoJSON Engine
            </div>
        </div>
    </x-slot>

    @php
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;
        $bisaKelolaLayer = $isAdmin || in_array('Kelola Layer', $aksesMenu);

        // AMBIL DATA BERKAS DI MEJA SAYA UNTUK DROPDOWN LINK
        $berkasDiMeja = \App\Models\Berkas::where('posisi_sekarang_user_id', auth()->id())
            ->where('status', 'Diproses')
            ->where('status_pengiriman', 'Diterima')
            ->orderBy('updated_at', 'desc')
            ->get();
    @endphp

    {{-- KONTANER UTAMA PETA --}}
    <div class="relative w-full bg-gray-200 overflow-hidden" style="height: calc(100vh - 140px); min-height: 600px;">
        
        {{-- LOADING INDICATOR --}}
        <div id="map-loading" class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[4000] bg-white/95 px-6 py-3 rounded-full font-bold shadow-2xl text-gray-700 flex items-center border border-gray-100 backdrop-blur-sm">
            <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-xl mr-3"></i> 
            <span id="loading-text">Memuat Data Spasial...</span>
        </div>

        {{-- PANEL FILTER PETA --}}
        <div class="absolute top-4 right-4 z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-[320px] transition-all">
            <h6 class="font-bold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                <i class="fa-solid fa-filter text-indigo-600 mr-2"></i> Filter & Alat
            </h6>
            
            <div class="space-y-3 mb-3">
                <div>
                    <label class="text-[11px] font-bold text-gray-600 mb-1 block uppercase tracking-wider">Pencarian Cepat</label>
                    <div class="relative">
                        <input type="text" id="searchMap" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pr-8" placeholder="Ketik NIB / No Berkas...">
                        <button onclick="document.getElementById('searchMap').value=''; loadData();" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4">
                <button onclick="loadData()" class="w-full bg-indigo-100 text-indigo-700 text-xs font-bold py-2 rounded-md hover:bg-indigo-200 transition shadow-sm flex items-center justify-center">
                    <i class="fa-solid fa-search mr-1.5"></i> Cari Data
                </button>
                
                @if($bisaKelolaLayer)
                <button onclick="bukaModal('modalUploadShp')" class="w-full bg-emerald-600 text-white text-xs font-bold py-2 rounded-md hover:bg-emerald-700 transition shadow-sm flex items-center justify-center">
                    <i class="fa-solid fa-cloud-upload-alt mr-1.5"></i> Upload SHP
                </button>
                @endif
            </div>
        </div>

        {{-- PANEL LAYER AKTIF --}}
        <div class="absolute top-[260px] right-4 z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-[320px] transition-all">
            <h6 class="font-bold text-gray-800 mb-2 flex items-center justify-between text-sm border-b pb-2">
                <span><i class="fa-solid fa-layer-group text-indigo-600 mr-2"></i> Layer Aktif</span>
            </h6>
            
            <div id="layerList" class="max-h-[200px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                @forelse($layers as $layer)
                    @php $tL = strtolower($layer->tipe_layer ?? 'standar'); @endphp
                    <div class="flex items-center justify-between p-1.5 hover:bg-gray-50 rounded-md border border-transparent hover:border-gray-100 transition group">
                        <label class="flex items-center text-sm text-gray-700 cursor-pointer flex-1 truncate pr-2">
                            <input type="checkbox" class="layer-toggle mr-2 rounded w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 shadow-sm" 
                                   value="{{ $layer->id }}">
                            <span class="truncate font-medium">{{ $layer->nama_layer }}</span>
                        </label>
                        
                        <div class="flex items-center gap-2">
                            <button onclick="zoomToLayer({{ $layer->id }})" title="Fokuskan Peta ke Aset Ini" class="text-gray-400 hover:text-indigo-600 transition">
                                <i class="fa-solid fa-crosshairs"></i>
                            </button>
                            
                            @if($tL == 'utama')
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-blue-100 text-[10px] font-bold text-blue-800 border border-blue-200" title="Utama">U</div>
                            @elseif($tL == 'khusus')
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-purple-100 text-[10px] font-bold text-purple-800 border border-purple-200" title="Khusus">K</div>
                            @else
                                <div class="w-5 h-5 rounded border border-gray-300 shrink-0 shadow-sm" style="background-color: {{ $layer->warna_standar ?? $layer->warna ?? '#3388ff' }}" title="Standar"></div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 italic py-2 text-center">Belum ada layer SHP di database.</p>
                @endforelse
            </div>

            <div class="border-t border-gray-200 mt-3 pt-3">
                <div class="flex justify-between text-[11px] font-bold text-gray-600 mb-1">
                    <span>Transparansi Poligon</span> <span id="opacityVal">60%</span>
                </div>
                <input type="range" id="opacitySlider" min="0.1" max="1" step="0.1" value="0.6" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            </div>
        </div>

        {{-- LEGENDA UTAMA --}}
        <div class="absolute bottom-8 left-[15px] z-[1000] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 w-48 transition-all">
            <h6 class="font-bold text-gray-800 mb-2 border-b pb-1 text-[11px] uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-info-circle text-indigo-600 mr-1.5"></i> Legenda Tipe Hak
            </h6>
            <div class="space-y-2 text-[11px] text-gray-700 font-medium">
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#28a745] border border-gray-300 shadow-sm"></div> Hak Milik (HM)</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#ffc107] border border-gray-300 shadow-sm"></div> HGB</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#17a2b8] border border-gray-300 shadow-sm"></div> Hak Pakai (HP)</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#fd7e14] border border-gray-300 shadow-sm"></div> HGU</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#6f42c1] border border-gray-300 shadow-sm"></div> Tanah Wakaf</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-[4px] mr-2 bg-[#cccccc] border border-gray-300 shadow-sm"></div> Default / Lainnya</div>
            </div>
        </div>

        {{-- TOMBOL LOKASI SAYA --}}
        <button onclick="goToMyLocation()" class="absolute bottom-[260px] left-[15px] z-[1000] w-10 h-10 bg-white text-gray-700 rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 transition border border-gray-200" title="Cari Lokasi Saya">
            <i class="fa-solid fa-crosshairs text-lg"></i>
        </button>

        {{-- WADAH PETA UTAMA --}}
        <div id="main-map" style="width: 100%; height: 100%; z-index: 10;"></div>
    </div>

    {{-- MODAL UPLOAD SHP --}}
    @if($bisaKelolaLayer)
    <div id="modalUploadShp" class="fixed inset-0 z-[3000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-gray-100">
                <div class="bg-emerald-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg"><i class="fa-solid fa-cloud-upload-alt mr-2"></i> Upload & Import Data SHP</h3>
                    <button onclick="tutupModal('modalUploadShp')" class="hover:text-emerald-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form action="{{ route('map.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Layer Tujuan <span class="text-red-500">*</span></label>
                        <select name="layer_id" class="w-full text-sm border-gray-300 rounded-md focus:ring-emerald-500" required>
                            <option value="" disabled selected>-- Pilih Layer yang sudah dibuat --</option>
                            @foreach($layers as $layer)
                                <option value="{{ $layer->id }}">{{ $layer->nama_layer }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File (.ZIP) <span class="text-red-500">*</span></label>
                        <input type="file" name="file_zip" accept=".zip" required class="w-full text-sm border border-gray-300 rounded-md p-1.5 bg-gray-50 cursor-pointer">
                    </div>
                    <div class="pt-5 border-t flex justify-end gap-3 mt-6">
                        <button type="button" onclick="tutupModal('modalUploadShp')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition">Proses Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL ATRIBUT (UNTUK MENGISI/MENGEDIT DATA SETELAH MENGGAMBAR ATAU LINK) --}}
    <div id="modalAtribut" class="fixed inset-0 z-[4000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all border border-gray-100">
                <div class="bg-indigo-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg" id="modalAtributTitle"><i class="fa-solid fa-pen-to-square mr-2"></i> Informasi Data Aset</h3>
                    <button type="button" onclick="tutupModal('modalAtribut')" class="hover:text-indigo-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form id="formAtribut" class="p-6 space-y-4">
                    <input type="hidden" id="form_geometry">
                    <input type="hidden" id="form_asset_id">
                    <input type="hidden" id="form_mode" value="create">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Simpan ke Layer <span class="text-red-500">*</span></label>
                        <select id="form_layer_id" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500" required>
                            @foreach($layers as $layer)
                                <option value="{{ $layer->id }}">{{ $layer->nama_layer }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DROPDOWN LINK NOMER BERKAS --}}
                    <div class="bg-indigo-50/50 p-3 rounded-lg border border-indigo-100">
                        <label class="block text-xs font-bold text-indigo-700 uppercase mb-1"><i class="fa-solid fa-link mr-1"></i> Link Nomor Berkas</label>
                        <select id="form_no_berkas" class="w-full text-sm border-indigo-200 rounded-md focus:ring-indigo-500 bg-white font-semibold text-gray-700 shadow-sm cursor-pointer">
                            <option value="">-- Tidak di-link / Pilih Berkas di Meja Saya --</option>
                            @foreach($berkasDiMeja as $b)
                                <option value="{{ $b->nomer_berkas }}">{{ $b->nomer_berkas }} - {{ Str::limit($b->nama_pemohon, 25) }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">*Pilih nomor berkas agar area peta ini terhubung secara interaktif ke Detail Berkas.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">NIB</label>
                            <input type="text" id="form_nib" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipe Hak <span class="text-red-500">*</span></label>
                            <select id="form_tipehak" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500" required>
                                <option value="Hak Milik">Hak Milik</option>
                                <option value="Hak Guna Bangunan">Hak Guna Bangunan (HGB)</option>
                                <option value="Hak Pakai">Hak Pakai (HP)</option>
                                <option value="Hak Guna Usaha">Hak Guna Usaha (HGU)</option>
                                <option value="Hak Pengelolaan">Hak Pengelolaan (HPL)</option>
                                <option value="Wakaf">Tanah Wakaf</option>
                                <option value="Lainnya">Lainnya / Tidak Diketahui</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Luas (M²)</label>
                            <input type="number" id="form_luas" step="0.01" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penggunaan</label>
                            <input type="text" id="form_penggunaan" placeholder="Ex: Sawah, Perumahan" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Desa / Kelurahan</label>
                            <input type="text" id="form_kelurahan" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kecamatan</label>
                            <input type="text" id="form_kecamatan" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan Tambahan</label>
                        <textarea id="form_keterangan" rows="2" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500"></textarea>
                    </div>
                    
                    <div class="pt-4 border-t flex justify-end gap-3 mt-4">
                        <button type="button" onclick="tutupModal('modalAtribut')" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                        <button type="button" onclick="simpanAtributAset()" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    {{-- Leaflet JS & CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- Geoman untuk Edit/Draw Peta --}}
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; margin-left: 15px !important; margin-top: 15px !important; }
        .leaflet-control-zoom a { color: #4f46e5 !important; }
        .leaflet-control-layers { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; border-radius: 8px !important; margin-bottom: 25px !important; margin-left: 215px !important; }
    </style>

    <script>
        // ==========================================
        // VARIABEL GLOBAL PENTING (DILARANG DIHAPUS)
        // ==========================================
        var map, geoJsonLayer, userMarker;
        var currentOpacity = 0.6;
        var abortController = null; // Menghindari error JS
        var fetchTimeout = null;    // Menghindari error JS

        function bukaModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

        // Fitur Lokasi Saya
        window.goToMyLocation = function() {
            if (navigator.geolocation) {
                Swal.fire({ title: 'Mencari Lokasi...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                
                const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };

                navigator.geolocation.getCurrentPosition(
                    position => {
                        Swal.close();
                        var lat = position.coords.latitude;
                        var lng = position.coords.longitude;
                        map.flyTo([lat, lng], 18, { animate: true, duration: 1.5 });
                        
                        if(userMarker) map.removeLayer(userMarker);
                        userMarker = L.marker([lat, lng]).addTo(map)
                            .bindPopup('<b class="text-indigo-600"><i class="fa-solid fa-street-view mr-1"></i> Lokasi Anda Saat Ini</b>')
                            .openPopup();
                    }, 
                    error => {
                        Swal.close();
                        let pesanError = 'Tidak dapat mengakses lokasi. Pastikan GPS/Location aktif dan diizinkan di browser.';
                        Swal.fire('Gagal', pesanError, 'error');
                    }, 
                    options
                );
            } else {
                Swal.fire('Informasi', 'Geolocation tidak didukung oleh browser Anda.', 'warning');
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            
            @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{!! session('success') !!}' }); @endif
            @if(session('error')) Swal.fire({ icon: 'error', title: 'Gagal Memproses!', text: '{!! session('error') !!}' }); @endif

            // Inisialisasi Peta
            map = L.map('main-map', { 
                zoomControl: false, 
                maxZoom: 22,
                renderer: L.canvas() 
            }).setView([-7.8200, 112.0118], 13);
            
            L.control.zoom({ position: 'topleft' }).addTo(map);

            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxNativeZoom: 19, maxZoom: 22 });
            var googleSatLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{ maxNativeZoom: 20, maxZoom: 22 });
            osmLayer.addTo(map);
            L.control.layers({ "Peta Jalan (OSM)": osmLayer, "Satelit (Google)": googleSatLayer }, null, { position: 'bottomleft' }).addTo(map);
            
            function getColor(feature) {
                if(feature.properties && feature.properties.layer_color) { return feature.properties.layer_color; }
                return '#3388ff'; 
            }

            function highlightFeature(e) {
                var layer = e.target;
                layer.setStyle({ weight: 3, color: '#111827', fillOpacity: 0.8 });
                if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
            }
            
            function resetHighlight(e) { 
                geoJsonLayer.resetStyle(e.target); 
            }

            // ==========================================
            // KONTROL GEOMAN (DRAWING & EDITING TOOLS)
            // ==========================================
            @if($bisaKelolaLayer)
            map.pm.addControls({
                position: 'topleft',
                drawPolygon: true,
                drawMarker: false, drawPolyline: false, drawRectangle: false, drawCircle: false, drawCircleMarker: false, drawText: false,
                editMode: true,
                dragMode: true,
                cutPolygon: false,
                removalMode: true,
            });

            map.pm.setLang('id');

            map.on('pm:create', e => {
                let layer = e.layer;
                let geojson = layer.toGeoJSON();
                
                document.getElementById('form_mode').value = 'create';
                document.getElementById('form_geometry').value = JSON.stringify(geojson.geometry);
                document.getElementById('formAtribut').reset();
                document.getElementById('modalAtributTitle').innerHTML = '<i class="fa-solid fa-plus-circle mr-2"></i> Tambah Aset Baru';
                bukaModal('modalAtribut');

                map.removeLayer(layer); 
            });

            map.on('pm:remove', e => {
                let layer = e.layer;
                if(layer.feature && layer.feature.properties && layer.feature.properties.id) {
                    let assetId = layer.feature.properties.id;
                    fetch(`/map/asset/${assetId}`, { 
                        method: 'POST', 
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-HTTP-Method-Override': 'DELETE' } 
                    }).then(res => res.json()).then(data => { 
                        Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:3000, icon:'success', title:'Aset dihapus!'});
                    });
                }
            });
            @endif

            // ==========================================
            // RENDER GEOJSON LAYER & POPUP ASET
            // ==========================================
            geoJsonLayer = L.geoJSON(null, {
                style: function(feature) {
                    return { color: '#ffffff', fillColor: getColor(feature), weight: 1.5, opacity: 1, fillOpacity: currentOpacity };
                },
                onEachFeature: function(feature, layer) {
                    if(feature.properties && feature.properties.id) {
                        layer.pm.setOptions({ assetId: feature.properties.id });
                    }

                    layer.on('pm:edit', e => {
                        let newGeoJson = e.target.toGeoJSON();
                        let assetId = feature.properties.id;
                        
                        fetch(`/map/asset/${assetId}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-HTTP-Method-Override': 'PUT' },
                            body: JSON.stringify({ geometry: JSON.stringify(newGeoJson.geometry) })
                        }).then(res => res.json()).then(data => {
                            Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:2000, icon:'success', title:'Koordinat diupdate!'});
                        });
                    });

                    layer.on({ mouseover: highlightFeature, mouseout: resetHighlight });
                    
                    var p = feature.properties;
                    var raw = p.raw_data || p;
                    var tableRows = '';
                    var noBerkas = null; 
                    
                    for (var key in raw) {
                        if (raw.hasOwnProperty(key)) {
                            var val = raw[key];
                            
                            // Deteksi jika key berhubungan dengan Nomor Berkas 
                            let lowerKey = key.toLowerCase();
                            if (lowerKey === 'no_berkas' || lowerKey === 'nomer_berkas') {
                                if (val !== null && val !== '' && String(val).trim() !== '') {
                                    noBerkas = val;
                                }
                            }

                            if (val !== null && val !== '' && String(val).trim() !== '') {
                                var displayKey = key.replace(/_/g, ' ').toUpperCase();
                                tableRows += `
                                    <tr class="border-b border-gray-100 last:border-0 hover:bg-indigo-50/50 transition-colors">
                                        <td class="py-1.5 px-1 text-gray-500 font-bold text-[10px] w-2/5 align-top leading-tight break-words">${displayKey}</td>
                                        <td class="py-1.5 px-1 text-gray-900 font-semibold text-[11px] align-top whitespace-normal break-words leading-tight"><span class="mr-1 text-gray-400">:</span> ${val}</td>
                                    </tr>`;
                            }
                        }
                    }
                    if (tableRows === '') tableRows = '<tr><td colspan="2" class="text-center text-gray-400 py-3 text-xs italic">Tidak ada data atribut spasial</td></tr>';

                    var content = `
                        <div class="p-1 min-w-[280px] max-w-[340px] font-sans">
                            <div class="flex justify-between items-center border-b-2 border-indigo-500 pb-2 mb-2">
                                <h6 class="text-indigo-700 font-extrabold uppercase text-[12px] tracking-wider m-0 flex items-center">
                                    <i class="fa-solid fa-map-location-dot mr-2 text-indigo-500"></i> Informasi Aset
                                </h6>
                            </div>
                            <div class="max-h-[220px] overflow-y-auto pr-2 custom-scrollbar">
                                <table class="w-full text-left border-collapse"><tbody>${tableRows}</tbody></table>
                            </div>
                            
                            <div class="mt-3 pt-2.5 border-t border-gray-200 flex flex-col gap-2">
                                ${noBerkas ? `
                                    <a href="/berkas/search-link?no=${noBerkas}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-3 py-2.5 rounded-md font-bold transition-all flex items-center justify-center shadow-sm w-full">
                                        <i class="fa-solid fa-file-contract mr-2"></i> LIHAT DETAIL BERKAS
                                    </a>
                                ` : `
                                    <div class="text-[10px] text-orange-600 bg-orange-50 p-1.5 rounded text-center border border-orange-100 mb-1">
                                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Aset belum di-link. Silakan Edit Data.
                                    </div>
                                `}
                                
                                @if($bisaKelolaLayer)
                                <div class="flex justify-between gap-2">
                                    <button class="bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 px-3 py-1.5 rounded-md text-[10px] font-bold transition flex-1 flex items-center justify-center shadow-sm" onclick='editAtributAset(${JSON.stringify(raw)}, ${p.id}, ${p.layer_id})'>
                                        <i class="fa-solid fa-link mr-1.5"></i> Link / Edit Data
                                    </button>
                                    <button class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 px-3 py-1.5 rounded-md text-[10px] font-bold transition flex-1 flex items-center justify-center shadow-sm" onclick="deleteAsset(${p.id})">
                                        <i class="fa-solid fa-trash mr-1.5"></i> Hapus
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>`;
                    
                    layer.bindPopup(content);
                }
            }).addTo(map);

            // ==========================================
            // FUNGSI PENGAMBILAN DATA (AJAX)
            // ==========================================
            window.loadData = function() {
                clearTimeout(fetchTimeout); // Debounce requests
                
                fetchTimeout = setTimeout(function() {
                    var loading = document.getElementById('map-loading');
                    if(loading) loading.classList.remove('hidden');
                    
                    var selectedLayers = [];
                    document.querySelectorAll('.layer-toggle:checked').forEach(function(cb) { selectedLayers.push(cb.value); });

                    var params = new URLSearchParams({
                        north: map.getBounds().getNorth(), south: map.getBounds().getSouth(),
                        east: map.getBounds().getEast(), west: map.getBounds().getWest(),
                        zoom: map.getZoom(), 
                        search: document.getElementById('searchMap').value
                    });
                    selectedLayers.forEach(id => params.append('layers[]', id));

                    // Membatalkan request lama jika belum selesai
                    if (abortController) {
                        abortController.abort();
                    }
                    abortController = new AbortController();

                    fetch(`/map/api/data?${params.toString()}`, { signal: abortController.signal })
                        .then(res => res.json())
                        .then(data => {
                            geoJsonLayer.clearLayers();
                            if(data.features && data.features.length > 0) geoJsonLayer.addData(data);
                            if(loading) loading.classList.add('hidden');
                        })
                        .catch(err => { 
                            if (err.name !== 'AbortError' && loading) {
                                loading.classList.add('hidden'); 
                            }
                        });
                }, 300);
            };

            map.on('moveend', loadData); 
            document.querySelectorAll('.layer-toggle').forEach(cb => cb.addEventListener('change', loadData));

            document.getElementById('opacitySlider').addEventListener('input', function(e) {
                currentOpacity = e.target.value;
                document.getElementById('opacityVal').innerText = Math.round(currentOpacity * 100) + '%';
                geoJsonLayer.eachLayer(function(layer) { if (layer.setStyle) layer.setStyle({ fillOpacity: currentOpacity }); });
            });

            window.deleteAsset = function(id) {
                Swal.fire({ title: 'Hapus Aset?', text: "Bidang tanah akan dihapus permanen!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!' }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/map/asset/${id}`, { 
                            method: 'POST', 
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-HTTP-Method-Override': 'DELETE' } 
                        }).then(res => res.json()).then(data => { Swal.fire('Terhapus!', data.message, 'success'); loadData(); });
                    }
                });
            };

            // ==========================================
            // LOGIKA MODAL ATRIBUT & LINK BERKAS
            // ==========================================
            window.editAtributAset = function(raw, id, layerId) {
                document.getElementById('form_mode').value = 'update';
                document.getElementById('form_asset_id').value = id;
                document.getElementById('form_layer_id').value = layerId || '';
                
                let r = {};
                for(let key in raw) r[key.toUpperCase()] = raw[key];

                // LOGIKA AUTO-SELECT Nomer Berkas Link
                let noBerkasVal = r['NOMER_BERKAS'] || r['NO_BERKAS'] || '';
                let selectNoBerkas = document.getElementById('form_no_berkas');
                
                let optionExists = Array.from(selectNoBerkas.options).some(o => o.value === noBerkasVal);
                if (optionExists) {
                    selectNoBerkas.value = noBerkasVal;
                } else if (noBerkasVal !== '') {
                    // Jika ada nomor berkas tapi tidak ada di pilihan "Meja Saya", tambahkan opsi sementara
                    let newOption = new Option(noBerkasVal + ' (Riwayat / Diluar Meja)', noBerkasVal);
                    selectNoBerkas.add(newOption);
                    selectNoBerkas.value = noBerkasVal;
                } else {
                    selectNoBerkas.value = '';
                }

                document.getElementById('form_nib').value = r['NIB'] || '';
                
                let th = r['TIPEHAK'] || r['HAK'] || r['STATUS'] || 'Lainnya';
                let selectTipe = document.getElementById('form_tipehak');
                let optionExistsTh = Array.from(selectTipe.options).some(o => o.value.toLowerCase() === th.toLowerCase());
                selectTipe.value = optionExistsTh ? Array.from(selectTipe.options).find(o => o.value.toLowerCase() === th.toLowerCase()).value : 'Lainnya';
                
                document.getElementById('form_luas').value = r['LUAS'] || r['LUASTERTUL'] || '';
                document.getElementById('form_penggunaan').value = r['PENGGUNAAN'] || '';
                document.getElementById('form_kelurahan').value = r['KELURAHAN'] || r['DESA'] || '';
                document.getElementById('form_kecamatan').value = r['KECAMATAN'] || '';
                document.getElementById('form_keterangan').value = r['KETERANGAN'] || '';

                document.getElementById('modalAtributTitle').innerHTML = '<i class="fa-solid fa-link mr-2"></i> Link / Edit Data Atribut';
                bukaModal('modalAtribut');
            }

            window.simpanAtributAset = function() {
                let mode = document.getElementById('form_mode').value;
                let payload = {
                    layer_id: document.getElementById('form_layer_id').value,
                    nomer_berkas: document.getElementById('form_no_berkas').value, // <-- MENYIMPAN HASIL DROPDOWN LINK
                    nib: document.getElementById('form_nib').value,
                    tipehak: document.getElementById('form_tipehak').value,
                    luas: document.getElementById('form_luas').value,
                    penggunaan: document.getElementById('form_penggunaan').value,
                    kelurahan: document.getElementById('form_kelurahan').value,
                    kecamatan: document.getElementById('form_kecamatan').value,
                    keterangan: document.getElementById('form_keterangan').value,
                };

                let url = '';

                if (mode === 'create') {
                    url = '/map/store-draw';
                    payload.geometry = document.getElementById('form_geometry').value;
                } else {
                    let id = document.getElementById('form_asset_id').value;
                    url = `/map/asset/${id}`;
                    payload.is_attribute_update = true;
                    payload._method = 'PUT'; 
                }

                let btn = document.querySelector('#formAtribut button.bg-indigo-600');
                let originHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
                btn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                }).then(res => res.json()).then(data => {
                    tutupModal('modalAtribut');
                    Swal.fire('Berhasil!', data.message, 'success');
                    loadData(); 
                }).catch(err => {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                }).finally(() => {
                    btn.innerHTML = originHtml;
                    btn.disabled = false;
                });
            }

            window.zoomToLayer = function(layerId) {
                let cb = document.querySelector(`.layer-toggle[value="${layerId}"]`);
                if(cb && !cb.checked) { cb.checked = true; loadData(); }
                Swal.fire({ title: 'Mencari Kordinat...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

                fetch(`/map/layer-bounds/${layerId}`)
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if(data.success && data.bbox) {
                        let tempLayer = L.geoJSON(data.bbox);
                        map.fitBounds(tempLayer.getBounds(), { padding: [20, 20], maxZoom: 18 });
                    } else {
                        Swal.fire('Informasi', 'Layer ini tidak memiliki kordinat valid.', 'warning');
                    }
                });
            };

            loadData(); // Inisialisasi peta pertama kali

            // ==========================================
            // DETEKSI AUTO ZOOM DARI HALAMAN DATA ASET
            // ==========================================
            const urlParams = newSearchParams(window.location.search);
            const zoomAssetId = urlParams.get('zoom_asset');

            if (zoomAssetId) {
                Swal.fire({ title: 'Mencari Lokasi Aset...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                
                fetch(`/map/asset/${zoomAssetId}`)
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
                        if(data.geometry) {
                            let cbLayer = document.querySelector(`.layer-toggle[value="${data.layer_id}"]`);
                            if(cbLayer && !cbLayer.checked) { 
                                cbLayer.checked = true; 
                                loadData(); 
                            }

                            let tempLayer = L.geoJSON(data.geometry);
                            map.fitBounds(tempLayer.getBounds(), { padding: [50, 50], maxZoom: 20 });

                            let highlightLayer = L.geoJSON(data.geometry, {
                                style: { color: '#ef4444', weight: 5, fillColor: '#ef4444', fillOpacity: 0.6 }
                            }).addTo(map);

                            highlightLayer.bindPopup('<b class="text-red-600"><i class="fa-solid fa-crosshairs mr-1"></i> Aset yang Anda cari</b>').openPopup();

                            setTimeout(() => { 
                                map.removeLayer(highlightLayer); 
                            }, 5000);
                        }
                    })
                    .catch(err => {
                        Swal.close();
                        console.error('Gagal mengambil kordinat aset');
                    });
            }

        });
    </script>
    @endpush
</x-app-layout>