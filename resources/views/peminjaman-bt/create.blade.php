<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Peminjaman Buku Tanah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                
                <form method="POST" action="{{ route('peminjaman-bt.store') }}">
                    @csrf

                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <x-input-label for="nomor_berkas" :value="__('Cari Data dari Nomor Berkas (Opsional)')" class="text-blue-800 font-bold" />
                        
                        <div class="flex gap-2 mt-2">
                            <div class="w-full">
                                <x-text-input id="nomor_berkas" class="block w-full" type="text" name="nomor_berkas" 
                                    :value="old('nomor_berkas')" placeholder="Contoh: 123/2025" />
                            </div>
                            <button type="button" id="btn-cek" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm transition duration-150 ease-in-out font-semibold whitespace-nowrap">
                                <i class="fas fa-search mr-1"></i> Cek Data
                            </button>
                        </div>
                        <p class="text-xs text-blue-600 mt-2">
                            <i class="fas fa-info-circle"></i> Masukkan nomor berkas lalu klik "Cek Data" untuk mengisi otomatis.
                        </p>
                        <x-input-error :messages="$errors->get('nomor_berkas')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <x-input-label for="jenis_hak" :value="__('Jenis Hak')" />
                            <select id="jenis_hak" name="jenis_hak" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih --</option>
                                <option value="HM" {{ old('jenis_hak') == 'HM' ? 'selected' : '' }}>Hak Milik (HM)</option>
                                <option value="HGB" {{ old('jenis_hak') == 'HGB' ? 'selected' : '' }}>Hak Guna Bangunan (HGB)</option>
                                <option value="HP" {{ old('jenis_hak') == 'HP' ? 'selected' : '' }}>Hak Pakai (HP)</option>
                                <option value="HGU" {{ old('jenis_hak') == 'HGU' ? 'selected' : '' }}>Hak Guna Usaha (HGU)</option>
                                <option value="Wakaf" {{ old('jenis_hak') == 'Wakaf' ? 'selected' : '' }}>Wakaf</option>
                                <option value="HPL" {{ old('jenis_hak') == 'HPL' ? 'selected' : '' }}>Hak Pengelolaan (HPL)</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis_hak')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="nomor_hak" :value="__('Nomor Hak')" />
                            <x-text-input id="nomor_hak" class="block mt-1 w-full" type="text" name="nomor_hak" 
                                :value="old('nomor_hak')" placeholder="Contoh: 00123" required />
                            <x-input-error :messages="$errors->get('nomor_hak')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <x-input-label for="kecamatan_id" :value="__('Kecamatan')" />
                            <select id="kecamatan_id" name="kecamatan_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $kec)
                                    <option value="{{ $kec->id }}" {{ old('kecamatan_id') == $kec->id ? 'selected' : '' }}>
                                        {{ $kec->nama_kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="desa_id" :value="__('Desa')" />
                            <select id="desa_id" name="desa_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih Desa --</option>
                                @foreach($desas as $desa)
                                    <option value="{{ $desa->id }}" data-kecamatan="{{ $desa->kecamatan_id }}" 
                                        {{ old('desa_id') == $desa->id ? 'selected' : '' }}>
                                        {{ $desa->nama_desa }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('desa_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="status" :value="__('Status Buku Tanah')" />
                        <select id="status" name="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-medium" required>
                            <option value="Ditemukan" {{ old('status') == 'Ditemukan' ? 'selected' : '' }}>Ditemukan</option>
                            <option value="Surat Tugas 1" {{ old('status') == 'Surat Tugas 1' ? 'selected' : '' }}>Surat Tugas 1 (Sedang Dipinjam)</option>
                            <option value="Surat Tugas 2" {{ old('status') == 'Surat Tugas 2' ? 'selected' : '' }}>Surat Tugas 2</option>
                            <option value="Buku Tanah Pengganti" {{ old('status') == 'Buku Tanah Pengganti' ? 'selected' : '' }}>Buku Tanah Pengganti (BTP)</option>
                            <option value="Blokir" {{ old('status') == 'Blokir' ? 'selected' : '' }}>Blokir / Sita</option>
                            <option value="Warkah" {{ old('status') == 'Warkah' ? 'selected' : '' }}>Sedang di Warkah</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="catatan" :value="__('Catatan Tambahan (Opsional)')" />
                        <textarea id="catatan" name="catatan" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-24" placeholder="Keterangan tambahan jika ada...">{{ old('catatan') }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-100 pt-4">
                        <a href="{{ route('peminjaman-bt.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                            Batal
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Data') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputNoBerkas = document.getElementById('nomor_berkas');
            const btnCek = document.getElementById('btn-cek');
            const selectKecamatan = document.getElementById('kecamatan_id');
            const selectDesa = document.getElementById('desa_id');
            const originalDesaOptions = Array.from(selectDesa.options);

            // 1. Filter Desa
            function filterDesa() {
                const selectedKecId = selectKecamatan.value;
                const currentDesaValue = selectDesa.value;
                selectDesa.innerHTML = '';

                originalDesaOptions.forEach(option => {
                    const desaKecId = option.getAttribute('data-kecamatan');
                    if (option.value === "" || selectedKecId === "" || desaKecId == selectedKecId) {
                        selectDesa.appendChild(option);
                    }
                });

                if ([...selectDesa.options].some(o => o.value === currentDesaValue)) {
                    selectDesa.value = currentDesaValue;
                } else {
                    selectDesa.value = "";
                }
            }
            selectKecamatan.addEventListener('change', filterDesa);
            filterDesa(); // Init

            // 2. Fungsi Cek Berkas (AJAX)
            function cekBerkas() {
                const noBerkas = inputNoBerkas.value.trim();
                
                if(!noBerkas) {
                    alert('Silakan isi Nomor Berkas terlebih dahulu.');
                    return;
                }

                // UI Loading
                const originalText = btnCek.innerHTML;
                btnCek.innerHTML = '<span class="animate-pulse">Memuat...</span>';
                btnCek.disabled = true;

                // Gunakan URL yang sama dengan route
                const url = `{{ route('ajax.cek-berkas-bt') }}?nomor_berkas=${encodeURIComponent(noBerkas)}`;

                fetch(url)
                    .then(response => {
                        // Controller baru sekarang mengembalikan JSON 200 OK bahkan jika error (agar bisa dibaca di sini)
                        // Namun jika masih ada 500 fatal error yang lolos, kita tangkap di catch
                        if (!response.ok) {
                            throw new Error(`HTTP Error: ${response.status} (${response.statusText})`);
                        }
                        return response.json();
                    })
                    .then(res => {
                        if(res.success) {
                            // --- AUTO FILL ---
                            if(res.data.jenis_hak) document.getElementById('jenis_hak').value = res.data.jenis_hak;
                            if(res.data.nomor_hak) document.getElementById('nomor_hak').value = res.data.nomor_hak;
                            if(res.data.kecamatan_id) {
                                selectKecamatan.value = res.data.kecamatan_id;
                                filterDesa(); // Trigger filter
                            }
                            // Set Desa dengan delay agar opsi tersedia
                            if(res.data.desa_id) {
                                setTimeout(() => {
                                    selectDesa.value = res.data.desa_id;
                                }, 50);
                            }
                            alert('Data Ditemukan! Form telah diisi.');
                        } else {
                            // Tampilkan pesan error spesifik dari server
                            alert(res.message);
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        alert('TERJADI KESALAHAN SISTEM:\n' + error.message + '\n\nSilakan cek Console Browser (F12) untuk detail, atau lapor admin.');
                    })
                    .finally(() => {
                        btnCek.innerHTML = originalText;
                        btnCek.disabled = false;
                    });
            }

            btnCek.addEventListener('click', cekBerkas);
            inputNoBerkas.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    cekBerkas();
                }
            });
        });
    </script>
</x-app-layout>