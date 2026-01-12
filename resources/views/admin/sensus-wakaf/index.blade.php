<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data & Peta Sensus Wakaf') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        #map { height: 500px; width: 100%; border-radius: 0.5rem; z-index: 1; }
        /* Style Tabel sederhana */
        .table-responsive { overflow-x: auto; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Peta Sebaran</h3>
                <div id="loadingMap" class="text-center text-gray-500 py-10">Memuat Peta...</div>
                <div id="map" class="hidden"></div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Filter & Data List</h3>

                <form method="GET" action="{{ route('sensus-wakaf.index') }}" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <x-input-label for="pengenal" :value="__('Cari Nama Wakaf')" />
                            <x-text-input id="pengenal" class="block mt-1 w-full text-sm" type="text" name="pengenal" :value="request('pengenal')" placeholder="Contoh: Masjid Al..." />
                        </div>

                        <div>
                            <x-input-label for="kecamatan" :value="__('Kecamatan')" />
                            <x-text-input id="kecamatan" class="block mt-1 w-full text-sm" type="text" name="kecamatan" :value="request('kecamatan')" placeholder="Nama Kecamatan" />
                        </div>

                        <div>
                            <x-input-label for="desa" :value="__('Desa')" />
                            <x-text-input id="desa" class="block mt-1 w-full text-sm" type="text" name="desa" :value="request('desa')" placeholder="Nama Desa" />
                        </div>

                        <div>
                            <x-input-label for="penggunaan" :value="__('Jenis Penggunaan')" />
                            <select name="penggunaan" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">Semua Jenis</option>
                                @foreach($listPenggunaan as $jenis)
                                    <option value="{{ $jenis }}" {{ request('penggunaan') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="afiliasi" :value="__('Afiliasi')" />
                            <select name="afiliasi" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">Semua Afiliasi</option>
                                @foreach($listAfiliasi as $afil)
                                    <option value="{{ $afil }}" {{ request('afiliasi') == $afil ? 'selected' : '' }}>{{ $afil }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('sensus-wakaf.index') }}" class="mr-3 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Reset
                        </a>
                        <x-primary-button>
                            {{ __('Terapkan Filter') }}
                        </x-primary-button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Wakaf (Pengenal)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Tanah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Afiliasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi (Desa/Kec)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($dataWakaf as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->pengenal }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->penggunaan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->status_tanah }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->afiliasi }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->desa }}, {{ $item->kecamatan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-xs">
                                        {{ $item->latitude }}, {{ $item->longitude }}
                                        <br>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{$item->latitude}},{{$item->longitude}}" target="_blank" class="text-blue-600 hover:underline">Lihat GMap</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $dataWakaf->links() }} 
                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Setup Map
            var map = L.map('map').setView([-7.715, 112.205], 10);
            
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Group untuk MarkerCluster (Ini kuncinya agar tidak berat)
            var markers = L.markerClusterGroup();

            fetch("{{ route('sensus-wakaf.data') }}")
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        var popupContent = `
                            <div class="text-sm p-1">
                                <strong class="text-indigo-600 block mb-1 text-base">${item.pengenal}</strong>
                                <table class="w-full text-xs">
                                    <tr><td class="font-semibold pr-2">Jenis:</td><td>${item.penggunaan}</td></tr>
                                    <tr><td class="font-semibold pr-2">Status:</td><td>${item.status_tanah}</td></tr>
                                    <tr><td class="font-semibold pr-2">Afiliasi:</td><td>${item.afiliasi}</td></tr>
                                    <tr><td class="font-semibold pr-2">Desa:</td><td>${item.desa}</td></tr>
                                    <tr><td class="font-semibold pr-2">Kecamatan:</td><td>${item.kecamatan}</td></tr>
                                </table>
                            </div>
                        `;

                        var marker = L.marker([item.latitude, item.longitude])
                            .bindPopup(popupContent);
                        
                        // Tambahkan marker ke dalam cluster group, BUKAN langsung ke map
                        markers.addLayer(marker);
                    });

                    // Tambahkan cluster group ke map
                    map.addLayer(markers);

                    // Sembunyikan loading, tampilkan map
                    document.getElementById('loadingMap').classList.add('hidden');
                    document.getElementById('map').classList.remove('hidden');

                    // Sesuaikan zoom agar memuat semua marker
                    if(data.length > 0){
                        map.fitBounds(markers.getBounds());
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingMap').innerText = "Gagal memuat data peta.";
                });
        });
    </script>
</x-app-layout>
