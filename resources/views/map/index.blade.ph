<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-map mr-2"></i> WebGIS / Peta Master
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            {{-- KOLOM KIRI: FORM IMPORT & LIST LAYER --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Form Import ZIP --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Import Layer SHP</h3>
                    <form action="{{ route('map.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <x-input-label value="Nama Layer" />
                                <x-text-input name="nama_layer" type="text" required class="w-full mt-1" />
                            </div>
                            <div>
                                <x-input-label value="File SHP (.ZIP)" />
                                <input type="file" name="file_zip" accept=".zip" required class="w-full mt-1 text-sm border border-gray-300 rounded">
                                <p class="text-[10px] text-gray-500 mt-1">Berisi .shp, .shx, .dbf, .prj</p>
                            </div>
                            <div>
                                <x-input-label value="Warna Utama" />
                                <input type="color" name="warna" value="#3388ff" class="w-full mt-1 h-10 rounded cursor-pointer">
                            </div>
                            <x-primary-button class="w-full justify-center">Proses Import</x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- List Layer & Setting Warna --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Daftar Layer</h3>
                    <ul class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                        @foreach($layers as $layer)
                            <li class="p-3 bg-gray-50 border border-gray-200 rounded flex flex-col gap-2">
                                <div class="flex justify-between items-center">
                                    <label class="flex items-center font-bold text-sm text-gray-800">
                                        {{-- Checkbox untuk On/Off Layer di Peta --}}
                                        <input type="checkbox" class="layer-toggle mr-2 rounded text-indigo-600 focus:ring-indigo-500" 
                                               value="{{ $layer->id }}" 
                                               data-warna="{{ $layer->warna }}"
                                               data-url="{{ route('map.tiles', ['layerId' => $layer->id, 'z' => '{z}', 'x' => '{x}', 'y' => '{y}']) }}">
                                        {{ $layer->nama_layer }}
                                    </label>
                                </div>
                                
                                {{-- Form Ubah Warna --}}
                                <form action="{{ route('map.updateWarna', $layer->id) }}" method="POST" class="flex items-center gap-2 mt-2">
                                    @csrf @method('PATCH')
                                    <input type="color" name="warna" value="{{ $layer->warna }}" class="w-8 h-8 rounded border border-gray-300">
                                    <button type="submit" class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-200">Ubah Warna</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- KOLOM KANAN: PETA UTAMA --}}
            <div class="lg:col-span-3 bg-white shadow-sm sm:rounded-lg p-2">
                <div id="main-map" class="w-full h-[700px] rounded z-10 border border-gray-300"></div>
            </div>

        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisiasi Peta (Pusat default ke Kediri)
            var map = L.map('main-map').setView([-7.8200, 112.0118], 12);

            // Base Map OSM
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Objek untuk menyimpan referensi layer yang sedang aktif
            var activeLayers = {};

            // Fungsi untuk menampilkan Vector Tile MVT
            function toggleLayer(checkbox) {
                var layerId = checkbox.value;
                var layerUrl = checkbox.getAttribute('data-url');
                var layerColor = checkbox.getAttribute('data-warna');

                if (checkbox.checked) {
                    // JIKA DICENTANG: Tambahkan Layer VectorGrid
                    var vectorLayer = L.vectorGrid.protobuf(layerUrl, {
                        vectorTileLayerStyles: {
                            // "default" adalah nama array MVT di query PostGIS kita
                            'default': {
                                weight: 1,
                                color: layerColor,   // Warna Garis
                                fillColor: layerColor, // Warna Fill
                                fillOpacity: 0.5,
                                fill: true
                            }
                        },
                        interactive: true // Agar bisa diklik jika ingin tambah popup nanti
                    });

                    vectorLayer.addTo(map);
                    activeLayers[layerId] = vectorLayer;

                } else {
                    // JIKA TIDAK DICENTANG: Hapus layer dari peta
                    if (activeLayers[layerId]) {
                        map.removeLayer(activeLayers[layerId]);
                        delete activeLayers[layerId];
                    }
                }
            }

            // Pasang event listener ke semua checkbox layer
            document.querySelectorAll('.layer-toggle').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    toggleLayer(this);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>