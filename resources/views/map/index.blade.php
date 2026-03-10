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
    @endphp

    {{-- KONTANER UTAMA PETA --}}
    <div class="relative w-full bg-gray-200 overflow-hidden" style="height: calc(100vh - 140px); min-height: 600px;">
        
        {{-- LOADING INDICATOR --}}
        <div id="map-loading" class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[2000] bg-white/95 px-6 py-3 rounded-full font-bold shadow-2xl text-gray-700 flex items-center border border-gray-100 backdrop-blur-sm">
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
                    <label class="text-[11px] font-bold text-gray-600 mb-1 block uppercase tracking-wider">Pencarian (Nama/NIB)</label>
                    <div class="relative">
                        <input type="text" id="searchMap" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pr-8" placeholder="Ketik kata kunci...">
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
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-blue-100 text-[10px] font-bold text-blue-800 border border-blue-200" title="Layar Utama">U</div>
                            @elseif($tL == 'khusus')
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-purple-100 text-[10px] font-bold text-purple-800 border border-purple-200" title="Layar Khusus">K</div>
                            @else
                                <div class="w-5 h-5 rounded border border-gray-300 shrink-0 shadow-sm" style="background-color: {{ $layer->warna_standar ?? $layer->warna ?? '#3388ff' }}" title="Layar Standar"></div>
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

        {{-- LEGENDA UTAMA (SINKRON DENGAN WARNA BACKEND MAPCONTROLLER) --}}
        <div class="absolute bottom-8 left-[15px] z-[1000] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 w-48 transition-all">
            <h6 class="font-bold text-gray-800 mb-2 border-b pb-1 text-[11px] uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-info-circle text-indigo-600 mr-1.5"></i> Legenda Tipe Hak
            </h6>
            <div class="space-y-1.5 text-[11px] text-gray-700 font-medium">
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#28a745] border border-gray-300"></div> Hak Milik (HM)</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#ffc107] border border-gray-300"></div> HGB</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#17a2b8] border border-gray-300"></div> Hak Pakai (HP)</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#fd7e14] border border-gray-300"></div> HGU</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#6f42c1] border border-gray-300"></div> Tanah Wakaf</div>
                <div class="flex items-center"><div class="w-3.5 h-3.5 rounded-[3px] mr-2 bg-[#000000] border border-gray-300"></div> Default / Lainnya</div>
            </div>
        </div>

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
                    <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg border border-blue-200 flex items-start">
                        <i class="fa-solid fa-circle-info mt-0.5 mr-2 text-blue-500 text-base"></i>
                        <div>
                            Pastikan Anda mengunggah file <b>.ZIP</b> yang didalamnya berisi lengkap komponen Shapefile (.shp, .shx, .dbf, .prj).
                        </div>
                    </div>

                    <div>
                        <label for="layer_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Layer Tujuan <span class="text-red-500">*</span></label>
                        <select id="layer_id" name="layer_id" class="w-full text-sm border-gray-300 rounded-md focus:ring-emerald-500" required>
                            <option value="" disabled selected>-- Pilih Layer yang sudah dibuat --</option>
                            @foreach($layers as $layer)
                                <option value="{{ $layer->id }}">{{ $layer->nama_layer }} ({{ ucfirst($layer->tipe_layer ?? 'standar') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File (.ZIP) <span class="text-red-500">*</span></label>
                        <input type="file" name="file_zip" accept=".zip" required class="w-full text-sm border border-gray-300 rounded-md p-1.5 bg-gray-50 cursor-pointer">
                    </div>
                    
                    <div class="pt-5 border-t flex justify-end gap-3 mt-6">
                        <button type="button" onclick="tutupModal('modalUploadShp')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                        <button type="submit" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin mr-2\'></i> Memproses...'; this.classList.add('opacity-70');" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition flex items-center">
                            <i class="fa-solid fa-upload mr-2"></i> Proses Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; margin-left: 15px !important; margin-top: 15px !important; }
        .leaflet-control-zoom a { color: #4f46e5 !important; }
        .leaflet-control-layers { border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important; border-radius: 8px !important; margin-bottom: 25px !important; margin-left: 215px !important; }
    </style>

    <script>
        function bukaModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

        document.addEventListener('DOMContentLoaded', function () {
            
            @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{!! session('success') !!}' }); @endif
            @if(session('error')) Swal.fire({ icon: 'error', title: 'Gagal Memproses!', text: '{!! session('error') !!}' }); @endif

            var map = L.map('main-map', { 
                zoomControl: false, 
                maxZoom: 22,
                renderer: L.canvas() // Eksekusi render canvas untuk peforma terbaik
            }).setView([-7.8200, 112.0118], 13);
            
            L.control.zoom({ position: 'topleft' }).addTo(map);

            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxNativeZoom: 19, maxZoom: 22 });
            var googleSatLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{ maxNativeZoom: 20, maxZoom: 22 });
            osmLayer.addTo(map);
            L.control.layers({ "Peta Jalan (OSM)": osmLayer, "Satelit (Google)": googleSatLayer }, null, { position: 'bottomleft' }).addTo(map);

            var currentOpacity = parseFloat(document.getElementById('opacitySlider').value);
            
            // FUNGSI WARNA YANG SANGAT RINGAN: Hanya mengambil variabel dari backend API
            function getColor(feature) {
                if(feature.properties && feature.properties.layer_color) {
                    return feature.properties.layer_color;
                }
                return '#3388ff'; // Warna default safety
            }

            function highlightFeature(e) {
                var layer = e.target;
                layer.setStyle({ weight: 3, color: '#111827', fillOpacity: 0.8 });
                if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
            }

            function resetHighlight(e) { geoJsonLayer.resetStyle(e.target); }

            var geoJsonLayer = L.geoJSON(null, {
                style: function(feature) {
                    return { 
                        color: '#ffffff', 
                        fillColor: getColor(feature), 
                        weight: 1.5, 
                        opacity: 1, 
                        fillOpacity: currentOpacity 
                    };
                },
                onEachFeature: function(feature, layer) {
                    layer.on({ mouseover: highlightFeature, mouseout: resetHighlight });
                    
                    var p = feature.properties;
                    var raw = p.raw_data || p;
                    var tipeHakTampil = p.kategori_hak || raw.TIPEHAK || raw.TIPE_HAK || '-';

                    var content = `
                        <div class="p-1 min-w-[220px]">
                            <h6 class="text-indigo-700 font-bold border-b border-gray-200 pb-1 mb-2">${p.name || 'Atribut Bidang'}</h6>
                            <table class="w-full text-xs text-gray-700">
                                <tr><td class="font-semibold py-1 w-1/3">Tipe Hak</td><td>: ${tipeHakTampil}</td></tr>
                                <tr><td class="font-semibold py-1">Luas</td><td>: ${raw.LUASTERTUL || raw.LUAS || 0} m²</td></tr>
                                <tr><td class="font-semibold py-1">Lokasi</td><td>: ${raw.KELURAHAN || raw.DESA || '-'}</td></tr>
                            </table>
                            @if($bisaKelolaLayer)
                            <div class="mt-3 flex justify-end gap-1 border-t pt-2">
                                <button class="bg-red-500 hover:bg-red-600 text-white text-[10px] px-3 py-1 rounded transition" onclick="deleteAsset(${p.id})">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </div>
                            @endif
                        </div>`;
                    layer.bindPopup(content);
                }
            }).addTo(map);

            var abortController = null;
            var fetchTimeout = null;

            window.loadData = function() {
                clearTimeout(fetchTimeout);
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

                    if (abortController) abortController.abort();
                    abortController = new AbortController();

                    fetch(`/map/api/data?${params.toString()}`, { signal: abortController.signal })
                        .then(res => res.json())
                        .then(data => {
                            geoJsonLayer.clearLayers();
                            if(data.features && data.features.length > 0) geoJsonLayer.addData(data);
                            if(loading) loading.classList.add('hidden');
                        })
                        .catch(err => { if (err.name !== 'AbortError' && loading) loading.classList.add('hidden'); });
                }, 350);
            };

            map.on('moveend', loadData); 
            document.querySelectorAll('.layer-toggle').forEach(cb => cb.addEventListener('change', loadData));

            document.getElementById('opacitySlider').addEventListener('input', function(e) {
                currentOpacity = e.target.value;
                document.getElementById('opacityVal').innerText = Math.round(currentOpacity * 100) + '%';
                geoJsonLayer.eachLayer(function(layer) { 
                    if (layer.setStyle) layer.setStyle({ fillOpacity: currentOpacity }); 
                });
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

            loadData();
        });
    </script>
    @endpush
</x-app-layout>