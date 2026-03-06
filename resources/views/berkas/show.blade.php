<x-app-layout>
    {{-- HEADER HALAMAN --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                 <a href="{{ route('ruang-kerja') }}" class="text-gray-400 hover:text-gray-600 mr-4">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Berkas: <span class="text-indigo-600">{{ $berkas->nomer_berkas }}</span>
                </h2>
            </div>
            
            {{-- Badge Status Saat Ini --}}
            <span class="px-3 py-1 text-sm font-bold rounded-full border
                @if($berkas->status == 'Selesai') bg-green-100 text-green-800 border-green-200
                @elseif($berkas->status == 'Pengumuman') bg-yellow-100 text-yellow-800 border-yellow-200
                @elseif($berkas->status == 'Berkas Kembali') bg-red-100 text-red-800 border-red-200
                @else bg-blue-100 text-blue-800 border-blue-200 @endif">
                <i class="fa-solid fa-flag mr-1"></i> Status: {{ $berkas->status }}
            </span>
        </div>
    </x-slot>

    {{-- KONTEN UTAMA --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- ================= KOLOM KIRI ================= --}}
            <div class="md:col-span-1 space-y-6">
                
                {{-- 1. INFORMASI BERKAS --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i>Informasi Berkas</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pemohon</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-bold">{{ $berkas->nama_pemohon }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->desa }}, {{ $berkas->kecamatan }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis & No. Hak</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->jenis_alas_hak }} - {{ $berkas->nomer_hak }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Permohonan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->jenisPermohonan->nama_permohonan ?? 'N/A' }}</dd>
                        </div>
                         <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi Saat Ini</dt>
                            <dd class="mt-1 text-sm font-semibold text-green-600 flex items-center">
                                <i class="fa-solid fa-user-check mr-2"></i> {{ $berkas->posisiSekarang->name ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- ================= KOLOM KANAN ================= --}}
            <div class="md:col-span-2 space-y-6">
                
                {{-- 3. DATA PENDUKUNG & PETA LOKASI --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fa-solid fa-map-location-dot mr-2 text-indigo-500"></i>Lampiran & Lokasi Peta</h3>
                    
                    {{-- Area Tombol Download PDF --}}
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        @if($berkas->file_sertipikat)
                            <a href="{{ asset('storage/' . $berkas->file_sertipikat) }}" target="_blank" class="flex items-center justify-center px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-md hover:bg-red-100 font-bold text-sm transition-all w-full shadow-sm">
                                <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Lihat Sertipikat
                            </a>
                        @else
                            <span class="flex items-center justify-center px-4 py-2 bg-gray-50 text-gray-400 border border-gray-200 rounded-md text-sm w-full cursor-not-allowed">
                                <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Sertipikat Kosong
                            </span>
                        @endif

                        @if($berkas->file_data_pendukung)
                            <a href="{{ asset('storage/' . $berkas->file_data_pendukung) }}" target="_blank" class="flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 font-bold text-sm transition-all w-full shadow-sm">
                                <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Lihat Data Pendukung
                            </a>
                        @else
                            <span class="flex items-center justify-center px-4 py-2 bg-gray-50 text-gray-400 border border-gray-200 rounded-md text-sm w-full cursor-not-allowed">
                                <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Pendukung Kosong
                            </span>
                        @endif
                    </div>

                    {{-- Area Peta --}}
                    @if($berkas->latitude && $berkas->longitude)
                        <div class="w-full h-[350px] rounded-md border border-gray-300 z-10 shadow-inner" id="map-detail"></div>
                        <div class="mt-2 text-sm text-gray-600 flex justify-between bg-gray-50 p-2 rounded border border-gray-200">
                            <span><b class="text-indigo-600">Latitude:</b> {{ $berkas->latitude }}</span>
                            <span><b class="text-indigo-600">Longitude:</b> {{ $berkas->longitude }}</span>
                        </div>
                    @else
                        <div class="w-full h-40 bg-gray-50 rounded-md border border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-500 p-4 text-center">
                            <i class="fa-solid fa-location-dot text-3xl mb-3 text-gray-300"></i>
                            <p class="text-sm font-medium">Data koordinat / peta tidak dilampirkan.</p>
                        </div>
                    @endif
                </div>

                {{-- 4. LINIMASA RIWAYAT --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2"><i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-500"></i>Linimasa Riwayat</h3>
                    <div class="relative border-l-2 border-gray-200 ml-3">
                        @forelse ($berkas->riwayat->sortByDesc('created_at') as $item)
                            <div class="mb-8 ml-6">
                                <span class="absolute flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full -left-4 ring-4 ring-white">
                                    @if($loop->last)
                                        <i class="fa-solid fa-file-circle-plus text-blue-600"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-right-arrow-left text-blue-600"></i>
                                    @endif
                                </span>
                                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="items-center justify-between sm:flex mb-2">
                                        <time class="mb-1 text-xs font-normal text-gray-500 sm:order-last sm:mb-0">{{ $item->created_at->isoFormat('dddd, D MMMM YYYY - HH:mm') }}</time>
                                        <p class="text-sm font-semibold text-gray-800">
                                            @if($loop->last)
                                                Berkas Dibuat oleh <span class="text-indigo-600">{{ $item->dariUser->name ?? 'N/A' }}</span>
                                            @else
                                                Aksi oleh <span class="text-indigo-600">{{ $item->dariUser->name ?? 'N/A' }}</span>
                                                @if($item->keUser && $item->dariUser->id !== $item->keUser->id)
                                                    diteruskan ke <span class="text-blue-600">{{ $item->keUser->name ?? 'N/A' }}</span>
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                    @if($item->catatan_pengiriman)
                                    <div class="p-3 mt-2 text-xs italic text-gray-800 bg-yellow-50 rounded-lg border border-yellow-200">
                                        Catatan: "{{ $item->catatan_pengiriman }}"
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="ml-6 text-gray-500 text-sm">
                                Belum ada riwayat pergerakan untuk berkas ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @if($berkas->latitude && $berkas->longitude)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var lat = {{ $berkas->latitude }};
                var lng = {{ $berkas->longitude }};

                var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                });

                var googleSatLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '&copy; Google Satellite'
                });

                var map = L.map('map-detail', {
                    center: [lat, lng],
                    zoom: 16,
                    layers: [googleSatLayer] // Tampilan default mode satelit untuk detail
                });

                var baseMaps = {
                    "Satelit (Google)": googleSatLayer,
                    "Peta Jalan (OSM)": osmLayer
                };
                L.control.layers(baseMaps).addTo(map);

                // Tambahkan Marker
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('<b>Lokasi Objek Berkas:</b><br>{{ $berkas->nomer_berkas }}')
                    .openPopup();

                setTimeout(function(){ map.invalidateSize(); }, 500);
            });
        </script>
    @endif
    @endpush
</x-app-layout>