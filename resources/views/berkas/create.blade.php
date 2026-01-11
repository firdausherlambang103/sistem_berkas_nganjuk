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
                {{-- TAMBAHKAN KODE INI UNTUK MENAMPILKAN PESAN ERROR/SUKSES --}}
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
                <form action="{{ route('berkas.store') }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- ================= KOLOM KIRI (DATA BERKAS) ================= --}}
                        <div class="space-y-6">
                        {{-- BARIS BARU: TAHUN & NOMER BERKAS --}}
                            <div class="grid grid-cols-3 gap-4">
                                {{-- Input Tahun --}}
                                <div class="col-span-1">
                                    <x-input-label for="tahun" value="Tahun" />
                                    <x-text-input id="tahun" name="tahun" type="number" class="mt-1 block w-full" :value="old('tahun', date('Y'))" required />
                                    <x-input-error :messages="$errors->get('tahun')" class="mt-2" />
                                </div>

                                {{-- Input Nomer Berkas --}}
                                <div class="col-span-2">
                                    <x-input-label for="nomer_berkas" value="Nomer Berkas" />
                                    <x-text-input id="nomer_berkas" name="nomer_berkas" type="text" class="mt-1 block w-full" :value="old('nomer_berkas')" required autofocus />
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
                            
                            {{-- [BARU] Input Status Buku Tanah --}}
                            <div>
                                <x-input-label for="status_buku_tanah" value="Ketersediaan Buku Tanah (Arsip)" />
                                <select id="status_buku_tanah" name="status_buku_tanah" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Ada" {{ old('status_buku_tanah') == 'Ada' ? 'selected' : '' }}>Ada (Sudah dibawa pemohon/tersedia)</option>
                                    <option value="Butuh" {{ old('status_buku_tanah') == 'Butuh' ? 'selected' : '' }}>Butuh (Perlu pinjam di arsip)</option>
                                </select>
                                <x-input-error :messages="$errors->get('status_buku_tanah')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">*Pilih "Butuh" agar muncul di menu Peminjaman Buku Tanah.</p>
                            </div>

                            {{-- Input Kecamatan --}}
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

                            {{-- Input Desa --}}
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
                                <x-text-input id="nomer_wa" name="nomer_wa" type="text" class="mt-1 block w-full bg-gray-50" :value="old('nomer_wa')" placeholder="Otomatis terisi jika pilih kuasa..." />
                                <x-input-error :messages="$errors->get('nomer_wa')" class="mt-2" />
                            </div>

                            {{-- ================= BAGIAN PENERIMA KUASA (QUICK ADD) ================= --}}
                            <div class="pt-4 border-t border-gray-200 mt-4" x-data="{ showQuickAdd: false }">
                                <div class="flex justify-between items-center mb-2">
                                    <x-input-label for="penerima_kuasa_id" value="Penerima Kuasa (Opsional)" />
                                    
                                    {{-- Tombol Toggle Quick Add --}}
                                    <button type="button" @click="showQuickAdd = !showQuickAdd" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold focus:outline-none">
                                        <i class="fa-solid fa-plus-circle"></i> Tambah Kuasa Baru
                                    </button>
                                </div>

                                {{-- FORM QUICK ADD (AJAX) --}}
                                <div x-show="showQuickAdd" x-transition class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-md shadow-sm" style="display: none;">
                                    <h4 class="text-sm font-bold text-indigo-800 mb-2">Entri Kuasa Baru (Cepat)</h4>
                                    <div class="grid grid-cols-1 gap-3 mb-3">
                                        <div>
                                            <input type="text" id="new_kode_kuasa" class="block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Kode (Mis: K-005)">
                                        </div>
                                        <div>
                                            <input type="text" id="new_nama_kuasa" class="block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Lengkap Kuasa">
                                        </div>
                                        <div>
                                            <input type="text" id="new_nomer_wa" class="block w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="No. WA (08xxx)">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="showQuickAdd = false" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Batal</button>
                                        <button type="button" id="btn-simpan-kuasa" class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-md hover:bg-indigo-700 font-bold shadow-sm transition ease-in-out duration-150">Simpan & Pilih</button>
                                    </div>
                                    <p id="quick-add-error" class="text-xs text-red-600 mt-2 font-bold hidden"></p>
                                </div>

                                {{-- DROPDOWN SELECT KUASA --}}
                                <select id="penerima_kuasa_id" name="penerima_kuasa_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="" data-wa="">-- Tanpa Kuasa (Pemohon Langsung) --</option>
                                    @if(isset($penerimaKuasas))
                                        @foreach ($penerimaKuasas as $kuasa)
                                            <option value="{{ $kuasa->id }}" 
                                                    data-wa="{{ $kuasa->nomer_wa }}" 
                                                    {{ old('penerima_kuasa_id') == $kuasa->id ? 'selected' : '' }}>
                                                {{ $kuasa->nama_kuasa }} ({{ $kuasa->kode_kuasa }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('penerima_kuasa_id')" class="mt-2" />
                            </div>
                            {{-- ================= END BAGIAN KUASA ================= --}}

                        </div>
                    </div>
                    
                    {{-- Input Catatan --}}
                    <div class="mt-6">
                        <x-input-label for="catatan" value="Catatan (Opsional)" />
                        <textarea id="catatan" name="catatan" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button><i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Simpan Berkas') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // ==========================================
            // 1. LOGIKA WILAYAH (KECAMATAN -> DESA)
            // ==========================================
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

            // ==========================================
            // 2. LOGIKA AUTO-FILL NOMER WA KUASA
            // ==========================================
            const selectKuasa = document.getElementById('penerima_kuasa_id');
            const inputWa = document.getElementById('nomer_wa');

            selectKuasa.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const waNumber = selectedOption.getAttribute('data-wa');
                
                if (waNumber) {
                    inputWa.value = waNumber;
                    // Efek visual highlight
                    inputWa.classList.add('bg-yellow-100');
                    setTimeout(() => inputWa.classList.remove('bg-yellow-100'), 800);
                } else {
                    // Jika pilih "Tanpa Kuasa", jangan hapus input WA jika user sudah mengetik manual sebelumnya
                    // Kecuali jika input WA sama dengan nilai lama dari kuasa sebelumnya.
                    // Untuk amannya, kita biarkan saja atau kosongkan sesuai preferensi. 
                    // Di sini kita kosongkan jika inputnya readonly/kosong.
                }
            });

            // ==========================================
            // 3. LOGIKA QUICK ADD KUASA (AJAX)
            // ==========================================
            const btnSimpan = document.getElementById('btn-simpan-kuasa');
            const errorMsg = document.getElementById('quick-add-error');

            btnSimpan.addEventListener('click', function() {
                const kode = document.getElementById('new_kode_kuasa').value;
                const nama = document.getElementById('new_nama_kuasa').value;
                const wa = document.getElementById('new_nomer_wa').value;

                errorMsg.classList.add('hidden');
                errorMsg.innerText = '';

                // Validasi Client Side
                if(!kode || !nama || !wa) {
                    errorMsg.innerText = 'Semua kolom (Kode, Nama, WA) wajib diisi!';
                    errorMsg.classList.remove('hidden');
                    return;
                }

                // UI Loading State
                const originalText = btnSimpan.innerText;
                btnSimpan.disabled = true;
                btnSimpan.innerText = 'Menyimpan...';

                // AJAX Request
                fetch("{{ route('berkas.store-kuasa-ajax') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        kode_kuasa_baru: kode,
                        nama_kuasa_baru: nama,
                        nomer_wa_baru: wa
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan validasi.');
                    }
                    return data;
                })
                .then(result => {
                    if (result.success) {
                        // 1. Tambahkan Option baru ke Select
                        const newOption = new Option(`${result.data.nama_kuasa} (${result.data.kode_kuasa})`, result.data.id);
                        newOption.setAttribute('data-wa', result.data.nomer_wa);
                        selectKuasa.add(newOption, undefined);
                        
                        // 2. Pilih Option tersebut
                        selectKuasa.value = result.data.id;

                        // 3. Trigger change agar WA terisi otomatis
                        selectKuasa.dispatchEvent(new Event('change'));

                        // 4. Reset Form
                        document.getElementById('new_kode_kuasa').value = '';
                        document.getElementById('new_nama_kuasa').value = '';
                        document.getElementById('new_nomer_wa').value = '';

                        // 5. Beritahu user & Tutup Form (Optional: klik tombol toggle lagi via JS)
                        alert('Penerima Kuasa berhasil ditambahkan dan dipilih!');
                        // Opsional: Sembunyikan form
                        // document.querySelector('[x-data]').__x.$data.showQuickAdd = false; // Cara akses Alpine dari luar agak tricky, biarkan user menutup manual atau gunakan alert.
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMsg.innerText = error.message;
                    errorMsg.classList.remove('hidden');
                })
                .finally(() => {
                    btnSimpan.disabled = false;
                    btnSimpan.innerText = originalText;
                });
            });
        });
    </script>
    @endpush
</x-app-layout>