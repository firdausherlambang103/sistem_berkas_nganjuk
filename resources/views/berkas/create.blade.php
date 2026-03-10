<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-file-circle-plus mr-2"></i>
                Tambah Berkas Baru
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-6 mb-0" role="alert">
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-6 mb-0" role="alert">
                        <strong class="font-bold">Gagal!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                {{-- Form dengan enctype multipart/form-data untuk upload file --}}
                <form action="{{ route('berkas.store') }}" method="POST" enctype="multipart/form-data" class="p-6 lg:p-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- ================= KOLOM KIRI (DATA BERKAS) ================= --}}
                        <div class="space-y-6">
                            <div class="grid grid-cols-3 gap-4">
                                {{-- Input Tahun --}}
                                <div class="col-span-1">
                                    <x-input-label for="tahun" value="Tahun" />
                                    <x-text-input id="tahun" name="tahun" type="number" class="mt-1 block w-full" :value="old('tahun', date('Y'))" required />
                                    <x-input-error :messages="$errors->get('tahun')" class="mt-2" />
                                </div>

                                {{-- Input Nomer Berkas --}}
                                <div class="col-span-2">
                                    <x-input-label for="nomer_berkas" value="Nomer Berkas (Kode)" />
                                    @php
                                        $defaultNomer = old('nomer_berkas') ?? ($isMitra ? $generatedCode : '');
                                    @endphp
                                    <x-text-input id="nomer_berkas" name="nomer_berkas" type="text" class="mt-1 block w-full {{ $isMitra ? 'bg-indigo-50 font-mono font-bold text-indigo-700 tracking-widest' : '' }}" :value="$defaultNomer" required autofocus />
                                    @if($isMitra)
                                        <p class="text-[10px] text-gray-500 mt-1">*Kode acak di-generate otomatis untuk Anda. Bisa diubah jika perlu.</p>
                                    @endif
                                    <x-input-error :messages="$errors->get('nomer_berkas')" class="mt-2" />
                                </div>
                            </div>
                            
                            {{-- Input Nama Pemohon --}}
                            <div>
                                <x-input-label for="nama_pemohon" value="Nama Pemohon (Sesuai KTP/Alas Hak)" />
                                <x-text-input id="nama_pemohon" name="nama_pemohon" type="text" class="mt-1 block w-full" :value="old('nama_pemohon')" required />
                                <x-input-error :messages="$errors->get('nama_pemohon')" class="mt-2" />
                            </div>

                            {{-- Input Jenis Alas Hak --}}
                            <div>
                                <x-input-label for="jenis_alas_hak" value="Jenis Alas Hak" />
                                <select id="jenis_alas_hak" name="jenis_alas_hak" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Jenis Hak --</option>
                                    <option value="Letter C" {{ old('jenis_alas_hak') == 'Letter C' ? 'selected' : '' }}>Letter C</option>
                                    <option value="SHM" {{ old('jenis_alas_hak') == 'SHM' ? 'selected' : '' }}>SHM (Sertipikat Hak Milik)</option>
                                    <option value="SHGB" {{ old('jenis_alas_hak') == 'SHGB' ? 'selected' : '' }}>SHGB (Sertipikat Hak Guna Bangunan)</option>
                                    <option value="SHGU" {{ old('jenis_alas_hak') == 'SHGU' ? 'selected' : '' }}>SHGU (Sertipikat Hak Guna Usaha)</option>
                                    <option value="SHP" {{ old('jenis_alas_hak') == 'SHP' ? 'selected' : '' }}>SHP (Sertipikat Hak Pakai)</option>
                                    <option value="SHW" {{ old('jenis_alas_hak') == 'SHW' ? 'selected' : '' }}>SHW (Sertipikat Hak Wakaf)</option>
                                    <option value="SK" {{ old('jenis_alas_hak') == 'SK' ? 'selected' : '' }}>SK (SK Pemberian Hak)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('jenis_alas_hak')" />
                            </div>

                            {{-- Input Nomer Hak --}}
                            <div>
                                <x-input-label for="nomer_hak" value="Nomer Hak" />
                                <x-text-input id="nomer_hak" name="nomer_hak" type="text" class="mt-1 block w-full" :value="old('nomer_hak')" required />
                                <x-input-error :messages="$errors->get('nomer_hak')" class="mt-2" />
                            </div>

                            {{-- Input Jenis Permohonan --}}
                            <div>
                                <x-input-label for="jenis_permohonan_id" value="Jenis Permohonan" />
                                <select id="jenis_permohonan_id" name="jenis_permohonan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Jenis Permohonan --</option>
                                    @foreach ($jenisPermohonans as $permohonan)
                                        <option value="{{ $permohonan->id }}" {{ old('jenis_permohonan_id') == $permohonan->id ? 'selected' : '' }}>
                                            {{ $permohonan->nama_permohonan }}
                                            @if($permohonan->memerlukan_ukur)
                                                (Perlu Ukur)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('jenis_permohonan_id')" class="mt-2" />
                            </div>
                        </div>

                        {{-- ================= KOLOM KANAN (LOKASI & KONTAK) ================= --}}
                        <div class="space-y-6">
                            
                            <div>
                                <x-input-label for="status_buku_tanah" value="Status Sertipikat / Buku Tanah" />
                                <select id="status_buku_tanah" name="status_buku_tanah" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Status --</option>
                                    <option value="Sertipikat Elektronik" {{ old('status_buku_tanah') == 'Sertipikat Elektronik' ? 'selected' : '' }}>Sertipikat Elektronik</option>
                                    <option value="Sertipikat Analog" {{ old('status_buku_tanah') == 'Sertipikat Analog' ? 'selected' : '' }}>Sertipikat Analog</option>
                                    <option value="Belum Sertipikat" {{ old('status_buku_tanah') == 'Belum Sertipikat' ? 'selected' : '' }}>Belum Sertipikat</option>
                                </select>
                                <x-input-error :messages="$errors->get('status_buku_tanah')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">*Pilih "Sertipikat Analog" jika memerlukan peminjaman buku tanah di arsip.</p>
                            </div>

                            <div>
                                <x-input-label for="kecamatan" value="Kecamatan" />
                                <select id="kecamatan" name="kecamatan" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Kecamatan --</option>
                                    @foreach ($kecamatans as $kecamatan)
                                        <option value="{{ $kecamatan->nama_kecamatan }}" data-id="{{ $kecamatan->id }}" {{ old('kecamatan') == $kecamatan->nama_kecamatan ? 'selected' : '' }}>
                                            {{ $kecamatan->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="desa" value="Desa" />
                                <select id="desa" name="desa" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                    <option value="" disabled selected>-- Pilih Kecamatan Terlebih Dahulu --</option>
                                </select>
                                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
                            </div>

                            {{-- Input Nomer WA --}}
                            <div>
                                <x-input-label for="nomer_wa" value="Nomer WhatsApp (Kontak yang bisa dihubungi)" />
                                @php
                                    $defaultWa = old('nomer_wa') ?? ($isMitra ? $user->nomer_wa : '');
                                @endphp
                                <x-text-input id="nomer_wa" name="nomer_wa" type="text" class="mt-1 block w-full bg-gray-50" :value="$defaultWa" placeholder="Otomatis terisi jika pilih kuasa..." />
                                <x-input-error :messages="$errors->get('nomer_wa')" class="mt-2" />
                            </div>

                            {{-- ================= BAGIAN PENERIMA KUASA ================= --}}
                            <div class="pt-4 border-t border-gray-200 mt-4">
                                @if($isMitra)
                                    <x-input-label value="Penerima Kuasa / Pengaju" />
                                    <div class="mt-1 p-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm flex items-center">
                                        <i class="fa-solid fa-user-tie text-gray-500 mr-3"></i>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">Otomatis didaftarkan atas nama Anda sebagai {{ $user->jabatan->nama_jabatan }}.</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="penerima_kuasa_id" value=""> 

                                @else
                                    <div x-data="{ showQuickAdd: false }">
                                        <div class="flex justify-between items-center mb-2">
                                            <x-input-label for="penerima_kuasa_id" value="Penerima Kuasa (Opsional)" />
                                            <button type="button" @click="showQuickAdd = !showQuickAdd" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold focus:outline-none">
                                                <i class="fa-solid fa-plus-circle"></i> Tambah Kuasa Baru
                                            </button>
                                        </div>

                                        {{-- FORM QUICK ADD --}}
                                        <div x-show="showQuickAdd" x-transition class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-md shadow-sm" style="display: none;">
                                            <h4 class="text-sm font-bold text-indigo-800 mb-2">Entri Kuasa Baru (Cepat)</h4>
                                            <div class="grid grid-cols-1 gap-3 mb-3">
                                                <div><input type="text" id="new_kode_kuasa" class="block w-full text-sm border-gray-300 rounded-md" placeholder="Kode (Mis: K-005)"></div>
                                                <div><input type="text" id="new_nama_kuasa" class="block w-full text-sm border-gray-300 rounded-md" placeholder="Nama Lengkap Kuasa"></div>
                                                <div><input type="text" id="new_nomer_wa" class="block w-full text-sm border-gray-300 rounded-md" placeholder="No. WA (08xxx)"></div>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="showQuickAdd = false" class="text-xs text-gray-500 font-medium">Batal</button>
                                                <button type="button" id="btn-simpan-kuasa" class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-md font-bold">Simpan & Pilih</button>
                                            </div>
                                            <p id="quick-add-error" class="text-xs text-red-600 mt-2 font-bold hidden"></p>
                                        </div>

                                        <select id="penerima_kuasa_id" name="penerima_kuasa_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="" data-wa="">-- Tanpa Kuasa (Pemohon Langsung) --</option>
                                            @if(isset($penerimaKuasas))
                                                @foreach ($penerimaKuasas as $kuasa)
                                                    <option value="{{ $kuasa->id }}" data-wa="{{ $kuasa->nomer_wa }}" {{ old('penerima_kuasa_id') == $kuasa->id ? 'selected' : '' }}>
                                                        {{ $kuasa->nama_kuasa }} ({{ $kuasa->kode_kuasa }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <x-input-error :messages="$errors->get('penerima_kuasa_id')" class="mt-2" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ================= BAGIAN KHUSUS MITRA ================= --}}
                    @if($isMitra)
                        <div class="col-span-1 md:col-span-2 mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-cloud-arrow-up mr-2 text-indigo-600"></i>Data Pendukung & Lokasi Objek</h3>
                            
                            <div class="space-y-8">
                                
                                {{-- Area Upload File (Dibagi menjadi 3 kolom: Sertipikat, Pendukung, Foto Kamera) --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-md">
                                        <x-input-label for="file_sertipikat" value="Upload Sertipikat (Format PDF, Maks 5MB)" class="font-bold" />
                                        <input type="file" id="file_sertipikat" name="file_sertipikat" accept="application/pdf" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 focus:outline-none" required />
                                        <x-input-error :messages="$errors->get('file_sertipikat')" class="mt-2" />
                                    </div>
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-md">
                                        <x-input-label for="file_data_pendukung" value="Upload Data Pendukung (Format PDF, Maks 5MB)" class="font-bold" />
                                        <input type="file" id="file_data_pendukung" name="file_data_pendukung" accept="application/pdf" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 focus:outline-none" required />
                                        <x-input-error :messages="$errors->get('file_data_pendukung')" class="mt-2" />
                                    </div>
                                    
                                    {{-- KAMERA LOKASI LANGSUNG (Menggunakan capture="environment") --}}
                                    <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-md">
                                        <x-input-label for="foto_lokasi" value="Foto Lokasi (Kamera HP)" class="font-bold text-indigo-800" />
                                        <input type="file" id="foto_lokasi" name="foto_lokasi" accept="image/*" capture="environment" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 focus:outline-none cursor-pointer" required />
                                        <p class="text-[10px] text-gray-500 mt-1.5 leading-tight">*Wajib mengambil foto langsung dari lokasi lahan / objek.</p>
                                        <img id="preview_foto" class="mt-3 hidden w-full h-32 object-cover rounded-md border border-gray-300 shadow-sm" alt="Preview Foto Lokasi"/>
                                        <x-input-error :messages="$errors->get('foto_lokasi')" class="mt-2" />
                                    </div>
                                </div>

                                {{-- Area Peta --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-2">
                                        <x-input-label value="Cari dan Pilih Titik Lokasi Objek Tanah di Peta" class="font-bold" />
                                        
                                        {{-- TOMBOL LOKASI SAYA --}}
                                        <button type="button" onclick="goToMyLocation()" class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm transition flex items-center w-max">
                                            <i class="fa-solid fa-location-crosshairs mr-1.5"></i> Gunakan Lokasi Saya
                                        </button>
                                    </div>
                                    
                                    <div id="map" class="w-full h-[400px] rounded-md border border-gray-300 z-10 shadow-inner"></div>
                                    <p class="text-[11px] text-gray-500 italic">*Gunakan tombol lokasi saya, kolom pencarian, atau geser Pin Biru pada peta secara manual untuk menentukan koordinat.</p>
                                    
                                    {{-- Koordinat --}}
                                    <div class="grid grid-cols-2 gap-4 pt-2">
                                        <div>
                                            <x-input-label for="latitude" value="Latitude" />
                                            <x-text-input id="latitude" name="latitude" type="text" class="mt-1 block w-full bg-gray-100 cursor-not-allowed text-sm" :value="old('latitude')" readonly required />
                                            <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="longitude" value="Longitude" />
                                            <x-text-input id="longitude" name="longitude" type="text" class="mt-1 block w-full bg-gray-100 cursor-not-allowed text-sm" :value="old('longitude')" readonly required />
                                            <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif
                    
                    {{-- Input Catatan --}}
                    <div class="mt-6">
                        <x-input-label for="catatan" value="Catatan Tambahan (Opsional)" />
                        <textarea id="catatan" name="catatan" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4 font-bold">Batal</a>
                        <x-primary-button><i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Simpan Berkas') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Library Maps --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    {{-- SweetAlert2 untuk notifikasi pencarian GPS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            @if($isMitra)
            
            // ================================================================
            // LOGIKA FOTO LOKASI (Preview Gambar)
            // ================================================================
            const fotoInput = document.getElementById('foto_lokasi');
            const previewImg = document.getElementById('preview_foto');
            if(fotoInput) {
                fotoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if(file) {
                        previewImg.src = URL.createObjectURL(file);
                        previewImg.classList.remove('hidden');
                    }
                });
            }

            // ================================================================
            // INISIALISASI PETA (LEAFLET.JS)
            // ================================================================
            var defaultLat = -7.8200;
            var defaultLng = 112.0118;

            var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
            var googleSatLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] });

            var map = L.map('map', { center: [defaultLat, defaultLng], zoom: 12, layers: [osmLayer] });
            L.control.layers({ "Peta Jalan (OSM)": osmLayer, "Satelit (Google)": googleSatLayer }).addTo(map);

            L.Control.geocoder({ defaultMarkGeocode: false, placeholder: "Cari daerah, desa, jalan..." })
            .on('markgeocode', function(e) {
                var center = e.geocode.center;
                map.setView(center, 16);
                marker.setLatLng(center);
                document.getElementById('latitude').value = center.lat.toFixed(7);
                document.getElementById('longitude').value = center.lng.toFixed(7);
            }).addTo(map);

            var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            marker.on('dragend', function (e) {
                var latLng = e.target.getLatLng();
                document.getElementById('latitude').value = latLng.lat.toFixed(7);
                document.getElementById('longitude').value = latLng.lng.toFixed(7);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(7);
            });

            setTimeout(function(){ map.invalidateSize(); }, 500);

            // ================================================================
            // LOGIKA LOKASI SAYA (GEOLOCATION GPS)
            // ================================================================
            window.goToMyLocation = function() {
                if (navigator.geolocation) {
                    Swal.fire({ title: 'Mencari Lokasi GPS...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                    
                    navigator.geolocation.getCurrentPosition(position => {
                        Swal.close();
                        var lat = position.coords.latitude;
                        var lng = position.coords.longitude;
                        var newLatLng = new L.LatLng(lat, lng);
                        
                        map.setView(newLatLng, 18);
                        marker.setLatLng(newLatLng);
                        
                        document.getElementById('latitude').value = lat.toFixed(7);
                        document.getElementById('longitude').value = lng.toFixed(7);

                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Lokasi Anda ditemukan!', showConfirmButton: false, timer: 3000 });
                    }, error => {
                        Swal.close();
                        Swal.fire('Gagal', 'Tidak dapat mengakses lokasi GPS. Pastikan GPS/Location aktif dan diizinkan pada browser/HP Anda.', 'error');
                    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
                } else {
                    Swal.fire('Informasi', 'Geolocation tidak didukung oleh browser Anda.', 'warning');
                }
            };
            @endif
            
            // ================================================================
            // 1. LOGIKA WILAYAH (KECAMATAN -> DESA)
            const kecamatanSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa');
            const oldDesa = "{{ old('desa') }}";

            function loadDesa(selectedOption) {
                if (!selectedOption || !selectedOption.dataset.id) {
                    desaSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kecamatan Terlebih Dahulu --</option>';
                    desaSelect.disabled = true;
                    return;
                }
                const kecamatanId = selectedOption.dataset.id;
                desaSelect.innerHTML = '<option value="" disabled selected>Memuat...</option>';
                desaSelect.disabled = true;

                fetch(`/api/get-desa?kecamatan_id=${kecamatanId}`)
                    .then(response => response.json())
                    .then(data => {
                        desaSelect.innerHTML = '<option value="" disabled selected>-- Pilih Desa --</option>';
                        data.forEach(desa => {
                            const option = document.createElement('option');
                            option.value = desa.nama_desa;
                            option.textContent = desa.nama_desa;
                            if (oldDesa === desa.nama_desa) {
                                option.selected = true;
                            }
                            desaSelect.appendChild(option);
                        });
                        desaSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching desa:', error);
                        desaSelect.innerHTML = '<option value="" disabled selected>Gagal memuat desa</option>';
                    });
            }

            kecamatanSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                loadDesa(selectedOption);
            });

            if (kecamatanSelect.value) {
                const selectedOption = kecamatanSelect.options[kecamatanSelect.selectedIndex];
                loadDesa(selectedOption);
            }

            // 2. LOGIKA AUTO-FILL WA (Hanya berjalan jika dropdown kuasa ada - Bukan Mitra)
            const selectKuasa = document.getElementById('penerima_kuasa_id');
            const inputWa = document.getElementById('nomer_wa');
            if(selectKuasa) {
                selectKuasa.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const waNumber = selectedOption.getAttribute('data-wa');
                    if (waNumber) {
                        inputWa.value = waNumber;
                        inputWa.classList.add('bg-yellow-100');
                        setTimeout(() => inputWa.classList.remove('bg-yellow-100'), 800);
                    }
                });

                // 3. LOGIKA QUICK ADD (AJAX)
                const btnSimpan = document.getElementById('btn-simpan-kuasa');
                if(btnSimpan){
                    btnSimpan.addEventListener('click', function() {
                        const kode = document.getElementById('new_kode_kuasa').value;
                        const nama = document.getElementById('new_nama_kuasa').value;
                        const wa = document.getElementById('new_nomer_wa').value;
                        const errorMsg = document.getElementById('quick-add-error');

                        errorMsg.classList.add('hidden');
                        
                        if(!kode || !nama || !wa) {
                            errorMsg.innerText = 'Semua kolom wajib diisi!';
                            errorMsg.classList.remove('hidden');
                            return;
                        }

                        const originalText = btnSimpan.innerText;
                        btnSimpan.disabled = true;
                        btnSimpan.innerText = 'Menyimpan...';

                        fetch("{{ route('berkas.store-kuasa-ajax') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ kode_kuasa_baru: kode, nama_kuasa_baru: nama, nomer_wa_baru: wa })
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan.');
                            return data;
                        })
                        .then(result => {
                            if (result.success) {
                                const newOption = new Option(`${result.data.nama_kuasa} (${result.data.kode_kuasa})`, result.data.id);
                                newOption.setAttribute('data-wa', result.data.nomer_wa);
                                selectKuasa.add(newOption, undefined);
                                selectKuasa.value = result.data.id;
                                selectKuasa.dispatchEvent(new Event('change'));
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Kuasa berhasil ditambahkan!', showConfirmButton: false, timer: 3000 });
                            }
                        })
                        .catch(error => {
                            errorMsg.innerText = error.message;
                            errorMsg.classList.remove('hidden');
                        })
                        .finally(() => {
                            btnSimpan.disabled = false;
                            btnSimpan.innerText = originalText;
                        });
                    });
                }
            }
        });
    </script>
    @endpush
</x-app-layout>