<x-app-layout>
    {{-- Kita sembunyikan header bawaan agar peta bisa lebih luas (Full Screen Look) --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-map-location-dot text-indigo-600 mr-2"></i> {{ __('WebGIS Master Peta') }}
            </h2>
            <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                <i class="fa-solid fa-server text-green-500 mr-1"></i> PostGIS MVT Engine
            </div>
        </div>
    </x-slot>

    <div class="relative w-full h-[calc(100vh-140px)] bg-gray-200 overflow-hidden">
        
        {{-- 1. LOADING INDICATOR MELAYANG --}}
        <div id="map-loading" class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[2000] bg-white/95 px-6 py-3 rounded-full font-bold shadow-2xl text-gray-700 flex items-center border border-gray-100 backdrop-blur-sm">
            <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-xl mr-3"></i> 
            <span id="loading-text">Memuat Jutaan Data...</span>
        </div>

        {{-- 2. PANEL FILTER & ALAT (KANAN ATAS) --}}
        <div class="absolute top-4 right-4 z-[1000] bg-white/90 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-72 transition-all hover:bg-white">
            <h6 class="font-bold text-gray-800 mb-3 flex items-center text-sm">
                <i class="fa-solid fa-magnifying-glass text-indigo-600 mr-2"></i> Alat & Pencarian
            </h6>
            
            <div class="space-y-3 mb-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Pencarian Aset</label>
                    <div class="relative">
                        <input type="text" id="searchMap" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pr-8" placeholder="Nama / NIB...">
                        <button onclick="document.getElementById('searchMap').value=''" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Tipe Hak (Filter)</label>
                    <select id="filterHak" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 py-1.5">
                        <option value="">Semua Hak</option>
                        <option value="HM">Hak Milik (HM)</option>
                        <option value="HGB">Hak Guna Bangunan (HGB)</option>
                        <option value="HGU">Hak Guna Usaha (HGU)</option>
                        <option value="HP">Hak Pakai (HP)</option>
                        <option value="WAKAF">Tanah Wakaf</option>
                    </select>
                </div>
            </div>
            
            <button onclick="alert('Fitur filter spesifik backend akan segera hadir!')" class="w-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold py-2 rounded-md hover:bg-indigo-100 transition shadow-sm mb-3">
                Terapkan Filter
            </button>

            @if(isset($bisaKelolaLayer) && $bisaKelolaLayer)
                <div class="border-t border-gray-200 pt-3">
                    <button onclick="bukaModalUpload()" class="w-full bg-emerald-600 text-white text-xs font-bold py-2 rounded-md hover:bg-emerald-700 transition shadow-md flex items-center justify-center">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload SHP Baru
                    </button>
                </div>
            @endif
        </div>

        {{-- 3. PANEL LAYER AKTIF (KANAN TENGAH) --}}
        <div class="absolute top-[280px] right-4 z-[1000] bg-white/90 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-72 transition-all hover:bg-white">
            <h6 class="font-bold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                <i class="fa-solid fa-layer-group text-indigo-600 mr-2"></i> Manajemen Layer
            </h6>
            
            <div id="layerList" class="max-h-[250px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                @forelse($layers as $layer)
                    <div class="flex items-center justify-between p-1.5 hover:bg-gray-50 rounded-md border border-transparent hover:border-gray-100 transition group">
                        <label class="flex items-center text-sm text-gray-700 cursor-pointer flex-1 truncate pr-2">
                            <input type="checkbox" class="layer-toggle mr-2 rounded w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" 
                                   value="{{ $layer->id }}" 
                                   data-warna="{{ $layer->warna }}"
                                   data-url="{{ url('/map/tiles/' . $layer->id . '/{z}/{x}/{y}.pbf') }}">
                            <span class="truncate font-medium">{{ $layer->nama_layer }}</span>
                        </label>
                        
                        @if(isset($bisaKelolaLayer) && $bisaKelolaLayer)
                            {{-- Color Picker Langsung Ganti Ajax --}}
                            <input type="color" class="layer-color-picker w-6 h-6 rounded cursor-pointer border border-gray-300 p-0 overflow-hidden shrink-0 opacity-70 group-hover:opacity-100 transition" 
                                   value="{{ $layer->warna }}" 
                                   data-id="{{ $layer->id }}" 
                                   title="Ubah Warna Layer">
                        @else
                            <div class="w-4 h-4 rounded-full border border-gray-300 shrink-0" style="background-color: {{ $layer->warna }}"></div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-gray-500 italic text-center py-2">Belum ada layer. Silakan upload.</p>
                @endforelse
            </div>

            {{-- Slider Opacity --}}
            <div class="border-t border-gray-200 mt-3 pt-3">
                <div class="flex justify-between text-xs font-bold text-gray-600 mb-1">
                    <span>Transparansi Peta</span>
                    <span id="opacityVal">60%</span>
                </div>
                <input type="range" id="opacitySlider" min="0.1" max="1" step="0.1" value="0.6" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
            </div>
        </div>

        {{-- 4. PANEL LEGENDA (KIRI BAWAH) --}}
        <div class="absolute bottom-6 left-4 z-[1000] bg-white/90 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 w-48 transition-all hover:bg-white">
            <h6 class="font-bold text-gray-800 mb-2 border-b pb-1 text-xs uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-list-ul text-indigo-600 mr-2"></i> Legenda
            </h6>
            <div class="space-y-1.5 text-xs text-gray-700 font-medium">
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#28a745] border border-gray-300 shadow-inner"></div> Hak Milik (HM)</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#ffc107] border border-gray-300 shadow-inner"></div> HGB</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#17a2b8] border border-gray-300 shadow-inner"></div> Hak Pakai (HP)</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#fd7e14] border border-gray-300 shadow-inner"></div> HGU</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#6f42c1] border border-gray-300 shadow-inner"></div> Tanah Wakaf</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#6c757d] border border-gray-300 shadow-inner"></div> Tanah Negara</div>
                <div class="flex items-center"><div class="w-4 h-4 rounded-sm mr-2 bg-[#3388ff] border border-gray-300 shadow-inner"></div> Layer Kustom</div>
            </div>
        </div>

        {{-- WADAH PETA UTAMA --}}
        <div id="main-map" class="w-full h-full z-10"></div>
    </div>

    {{-- 5. MODAL UPLOAD SHP (TAILWIND STYLE) --}}
    @if(isset($bisaKelolaLayer) && $bisaKelolaLayer)
    <div id="modalUploadShp" class="fixed inset-0 z-[3000] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" onclick="tutupModalUpload()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center" id="modal-title">
                        <i class="fa-solid fa-layer-group mr-2"></i> Upload SHP Baru
                    </h3>
                    <button type="button" onclick="tutupModalUpload()" class="text-indigo-100 hover:text-white focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <form action="{{ route('map.import') }}" method="POST" enctype="multipart/form-data" id="formUploadShp">
                    @csrf
                    <div class="bg-white px-6 py-5 space-y-4">
                        <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg border border-blue-200 flex items-start">
                            <i class="fa-solid fa-circle-info mt-0.5 mr-2 text-blue-500 text-base"></i>
                            <span>File ZIP harus berisi komponen lengkap shapefile (.shp, .shx, .dbf, .prj).</span>
                        </div>

                        <div>
                            <x-input-label value="Nama Layer Tampilan" />
                            <x-text-input name="nama_layer" type="text" required class="w-full mt-1 text-sm bg-gray-50" placeholder="Misal: Batas Desa 2026..." />
                        </div>
                        
                        <div>
                            <x-input-label value="Pilih File (.ZIP)" />
                            <input type="file" name="file_zip" accept=".zip" required class="w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md cursor-pointer">
                        </div>

                        <div>
                            <x-input-label value="Warna Default Layer" />
                            <div class="flex items-center mt-1 gap-3">
                                <input type="color" name="warna" value="#3388ff" class="w-12 h-10 rounded cursor-pointer border border-gray-300 p-0.5">
                                <span class="text-xs text-gray-500 italic">Bisa diubah nanti di panel manajemen layer.</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="submit" id="btnSubmitUpload" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                            <i class="fa-solid fa-cloud-arrow-up mr-2 mt-1"></i> Mulai Import
                        </button>
                        <button type="button" onclick="tutupModalUpload()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Batal
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
    
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Custom Scrollbar untuk kotak layer agar lebih rapi */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a0a0a0; }
        
        /* Menyembunyikan control zoom Leaflet default dan memindahkannya */
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important; }
        .leaflet-control-zoom a { color: #4f46e5 !important; background-color: rgba(255,255,255,0.9) !important; backdrop-filter: blur(4px); }
        .leaflet-control-zoom a:hover { background-color: #fff !important; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // === 1. INISIASI PETA ===
            var map = L.map('main-map', {
                zoomControl: false, // Matikan default agar bisa dipindah posisinya
                maxZoom: 22         // Super Zoom (Digital)
            }).setView([-7.8200, 112.0118], 13); // Pusat Default Kediri

            // Pindahkan Zoom Control ke Kanan Bawah agar tidak tertutup panel
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            // Layer Dasar (Basemaps) seperti di web_gis_kediri
            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'OSM', maxNativeZoom: 19, maxZoom: 22
            });

            var googleSatLayer = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{ 
                attribution: 'Google', maxNativeZoom: 20, maxZoom: 22
            });

            osmLayer.addTo(map);

            // Switcher Basemap Kiri Bawah
            L.control.layers({ 
                "Peta Jalan (OSM)": osmLayer, 
                "Satelit (Google)": googleSatLayer 
            }, null, { position: 'bottomleft' }).addTo(map);

            // === 2. LOGIKA MVT (VECTOR GRID) ===
            var activeLayers = {};
            var currentOpacity = parseFloat(document.getElementById('opacitySlider').value);

            function toggleLayer(checkbox) {
                var layerId = checkbox.value;
                var layerUrl = checkbox.getAttribute('data-url');
                var layerColor = checkbox.getAttribute('data-warna');

                if (checkbox.checked) {
                    showLoading();
                    
                    var vectorLayer = L.vectorGrid.protobuf(layerUrl, {
                        vectorTileLayerStyles: {
                            'default': function(properties, zoom) {
                                // Jika tabel properties punya TIPEHAK, warnai sesuai legenda, jika tidak pakai warna default layer
                                let tipe = properties.tipehak || properties.tipe_hak || '';
                                tipe = tipe.toString().toUpperCase();
                                let finalColor = layerColor;

                                if (tipe.includes('HM') || tipe.includes('MILIK')) finalColor = '#28a745';
                                else if (tipe.includes('HGB') || tipe.includes('BANGUNAN')) finalColor = '#ffc107';
                                else if (tipe.includes('HGU') || tipe.includes('USAHA')) finalColor = '#fd7e14';
                                else if (tipe.includes('HP') || tipe.includes('PAKAI')) finalColor = '#17a2b8';
                                else if (tipe.includes('WAKAF')) finalColor = '#6f42c1';

                                return {
                                    weight: 1.5,
                                    color: '#ffffff',     // Garis batas (putih)
                                    fillColor: finalColor, 
                                    fillOpacity: currentOpacity, // Opacity dari slider
                                    fill: true
                                }
                            }
                        },
                        interactive: true, // Agar bisa diklik
                        getFeatureId: function(f) { return f.properties.id; }
                    });

                    // Event Click pada bidang MVT
                    vectorLayer.on('click', function(e) {
                        let p = e.layer.properties;
                        let content = `
                            <div class="p-1 min-w-[200px]">
                                <h6 class="text-indigo-700 font-bold border-b border-gray-200 pb-1 mb-2">Informasi Bidang</h6>
                                <table class="w-full text-xs text-gray-700">
                                    <tr><td class="font-semibold py-1 w-1/3">ID</td><td>: ${p.id || '-'}</td></tr>
                                    <tr><td class="font-semibold py-1">Tipe Hak</td><td>: ${p.tipehak || p.tipe_hak || '-'}</td></tr>
                                    <tr><td class="font-semibold py-1">Luas</td><td>: ${p.luas || p.luastertul || '-'} m²</td></tr>
                                    <tr><td class="font-semibold py-1">Lokasi</td><td>: ${p.desa || p.kelurahan || '-'}</td></tr>
                                </table>
                            </div>
                        `;
                        L.popup().setLatLng(e.latlng).setContent(content).openOn(map);
                    });

                    vectorLayer.addTo(map);
                    activeLayers[layerId] = vectorLayer;
                    
                    // Sembunyikan loading setelah beberapa saat (MVT render sangat cepat)
                    setTimeout(hideLoading, 800);
                } else {
                    if (activeLayers[layerId]) {
                        map.removeLayer(activeLayers[layerId]);
                        delete activeLayers[layerId];
                    }
                }
            }

            // Daftarkan event checkbox
            document.querySelectorAll('.layer-toggle').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() { toggleLayer(this); });
            });

            // === 3. UPDATE OPACITY ===
            var opacitySlider = document.getElementById('opacitySlider');
            var opacityValText = document.getElementById('opacityVal');
            
            opacitySlider.addEventListener('input', function() {
                currentOpacity = this.value;
                opacityValText.innerText = Math.round(currentOpacity * 100) + '%';
            });

            opacitySlider.addEventListener('change', function() {
                // Untuk apply opacity di VectorGrid, cara paling aman adalah mematikan dan menyalakan ulang layer yang aktif
                showLoading();
                document.querySelectorAll('.layer-toggle:checked').forEach(function(checkbox) {
                    toggleLayer(checkbox); // Matikan
                    setTimeout(() => toggleLayer(checkbox), 50); // Nyalakan lagi dengan opacity baru
                });
                setTimeout(hideLoading, 500);
            });

            // === 4. UPDATE WARNA LAYER (AJAX) ===
            document.querySelectorAll('.layer-color-picker').forEach(function(picker) {
                picker.addEventListener('change', function() {
                    let layerId = this.getAttribute('data-id');
                    let newColor = this.value;
                    let csrfToken = document.querySelector('input[name="_token"]').value;

                    // Update attribute di checkbox
                    let checkbox = document.querySelector(`.layer-toggle[value="${layerId}"]`);
                    if(checkbox) checkbox.setAttribute('data-warna', newColor);

                    // Kirim ke server
                    fetch(`/map/update-warna/${layerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-HTTP-Method-Override': 'PATCH' // Laravel Method Spoofing
                        },
                        body: JSON.stringify({ warna: newColor })
                    }).then(res => {
                        // Jika layer sedang aktif, refresh layernya
                        if(checkbox && checkbox.checked) {
                            checkbox.checked = false; toggleLayer(checkbox);
                            setTimeout(() => { checkbox.checked = true; toggleLayer(checkbox); }, 100);
                        }
                        
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        Toast.fire({ icon: 'success', title: 'Warna layer diperbarui' });
                    });
                });
            });

            // === HELPER UI ===
            function showLoading() { document.getElementById('map-loading').classList.remove('hidden'); }
            function hideLoading() { document.getElementById('map-loading').classList.add('hidden'); }
            
            // Handle form upload submit
            var formUpload = document.getElementById('formUploadShp');
            if(formUpload) {
                formUpload.addEventListener('submit', function() {
                    let btn = document.getElementById('btnSubmitUpload');
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2 mt-1"></i> Memproses PostGIS...';
                    btn.disabled = true;
                    btn.classList.add('opacity-70', 'cursor-not-allowed');
                });
            }
        });

        // FUNGSI MODAL UPLOAD
        function bukaModalUpload() {
            document.getElementById('modalUploadShp').classList.remove('hidden');
        }
        function tutupModalUpload() {
            document.getElementById('modalUploadShp').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>