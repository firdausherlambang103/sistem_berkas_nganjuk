<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-file-pen mr-2"></i>
                Edit Berkas: {{ $berkas->nomer_berkas }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                {{-- Alerts --}}
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-6 mb-0" role="alert">
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-6 mb-0" role="alert">
                        <strong class="font-bold">Terjadi Kesalahan!</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('berkas.update', $berkas->id) }}" method="POST" enctype="multipart/form-data" class="p-6 lg:p-8">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- ================= KOLOM KIRI (DATA BERKAS) ================= --}}
                        <div class="space-y-6">
                            <div class="grid grid-cols-3 gap-4">
                                {{-- Input Tahun --}}
                                <div class="col-span-1">
                                    <x-input-label for="tahun" value="Tahun" />
                                    <x-text-input id="tahun" name="tahun" type="number" class="mt-1 block w-full" :value="old('tahun', $berkas->tahun)" required />
                                    <x-input-error :messages="$errors->get('tahun')" class="mt-2" />
                                </div>

                                {{-- Input Nomer Berkas --}}
                                <div class="col-span-2">
                                    <x-input-label for="nomer_berkas" value="Nomer Berkas (Kode)" />
                                    <x-text-input id="nomer_berkas" name="nomer_berkas" type="text" class="mt-1 block w-full bg-gray-50" :value="old('nomer_berkas', $berkas->nomer_berkas)" required />
                                    <x-input-error :messages="$errors->get('nomer_berkas')" class="mt-2" />
                                </div>
                            </div>
                            
                            {{-- Input Nama Pemohon --}}
                            <div>
                                <x-input-label for="nama_pemohon" value="Nama Pemohon (Sesuai KTP/Alas Hak)" />
                                <x-text-input id="nama_pemohon" name="nama_pemohon" type="text" class="mt-1 block w-full" :value="old('nama_pemohon', $berkas->nama_pemohon)" required />
                                <x-input-error :messages="$errors->get('nama_pemohon')" class="mt-2" />
                            </div>

                            {{-- Input Jenis Alas Hak --}}
                            <div>
                                <x-input-label for="jenis_alas_hak" value="Jenis Alas Hak" />
                                <select id="jenis_alas_hak" name="jenis_alas_hak" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled>-- Pilih Jenis Hak --</option>
                                    @php $jah = old('jenis_alas_hak', $berkas->jenis_alas_hak); @endphp
                                    <option value="Letter C" {{ $jah == 'Letter C' ? 'selected' : '' }}>Letter C</option>
                                    <option value="SHM" {{ $jah == 'SHM' ? 'selected' : '' }}>SHM (Sertipikat Hak Milik)</option>
                                    <option value="SHGB" {{ $jah == 'SHGB' ? 'selected' : '' }}>SHGB (Sertipikat Hak Guna Bangunan)</option>
                                    <option value="SHGU" {{ $jah == 'SHGU' ? 'selected' : '' }}>SHGU (Sertipikat Hak Guna Usaha)</option>
                                    <option value="SHP" {{ $jah == 'SHP' ? 'selected' : '' }}>SHP (Sertipikat Hak Pakai)</option>
                                    <option value="SHW" {{ $jah == 'SHW' ? 'selected' : '' }}>SHW (Sertipikat Hak Wakaf)</option>
                                    <option value="SK" {{ $jah == 'SK' ? 'selected' : '' }}>SK (SK Pemberian Hak)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('jenis_alas_hak')" />
                            </div>

                            {{-- Input Nomer Hak --}}
                            <div>
                                <x-input-label for="nomer_hak" value="Nomer Hak" />
                                <x-text-input id="nomer_hak" name="nomer_hak" type="text" class="mt-1 block w-full" :value="old('nomer_hak', $berkas->nomer_hak)" required />
                                <x-input-error :messages="$errors->get('nomer_hak')" class="mt-2" />
                            </div>

                            {{-- Input Jenis Permohonan --}}
                            <div>
                                <x-input-label for="jenis_permohonan_id" value="Jenis Permohonan" />
                                <select id="jenis_permohonan_id" name="jenis_permohonan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled>-- Pilih Jenis Permohonan --</option>
                                    @foreach ($jenisPermohonans as $permohonan)
                                        <option value="{{ $permohonan->id }}" {{ old('jenis_permohonan_id', $berkas->jenis_permohonan_id) == $permohonan->id ? 'selected' : '' }}>
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
                                    <option value="" disabled>-- Pilih Status --</option>
                                    @php $sbt = old('status_buku_tanah', $berkas->status_buku_tanah); @endphp
                                    <option value="Sertipikat Elektronik" {{ $sbt == 'Sertipikat Elektronik' ? 'selected' : '' }}>Sertipikat Elektronik</option>
                                    <option value="Sertipikat Analog" {{ $sbt == 'Sertipikat Analog' ? 'selected' : '' }}>Sertipikat Analog</option>
                                    <option value="Belum Sertipikat" {{ $sbt == 'Belum Sertipikat' ? 'selected' : '' }}>Belum Sertipikat</option>
                                </select>
                                <x-input-error :messages="$errors->get('status_buku_tanah')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">*Pilih "Sertipikat Analog" jika memerlukan peminjaman buku tanah di arsip.</p>
                            </div>

                            <div>
                                <x-input-label for="kecamatan" value="Kecamatan" />
                                <select id="kecamatan" name="kecamatan" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled>-- Pilih Kecamatan --</option>
                                    @foreach ($kecamatans as $kecamatan)
                                        <option value="{{ $kecamatan->nama_kecamatan }}" data-id="{{ $kecamatan->id }}" {{ old('kecamatan', $berkas->kecamatan) == $kecamatan->nama_kecamatan ? 'selected' : '' }}>
                                            {{ $kecamatan->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="desa" value="Desa" />
                                <select id="desa" name="desa" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                    <option value="" disabled>-- Pilih Kecamatan Terlebih Dahulu --</option>
                                </select>
                                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
                            </div>

                            {{-- Input Nomer WA --}}
                            <div>
                                <x-input-label for="nomer_wa" value="Nomer WhatsApp (Kontak yang bisa dihubungi)" />
                                <x-text-input id="nomer_wa" name="nomer_wa" type="text" class="mt-1 block w-full bg-gray-50" :value="old('nomer_wa', $berkas->nomer_wa)" placeholder="Otomatis terisi jika pilih kuasa..." />
                                <x-input-error :messages="$errors->get('nomer_wa')" class="mt-2" />
                            </div>

                            {{-- ================= BAGIAN PENERIMA KUASA ================= --}}
                            <div class="pt-4 border-t border-gray-200 mt-4">
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
                                        @foreach ($penerimaKuasas as $kuasa)
                                            <option value="{{ $kuasa->id }}" data-wa="{{ $kuasa->nomer_wa }}" {{ old('penerima_kuasa_id', $berkas->penerima_kuasa_id) == $kuasa->id ? 'selected' : '' }}>
                                                {{ $kuasa->nama_kuasa }} ({{ $kuasa->kode_kuasa }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('penerima_kuasa_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= BAGIAN KHUSUS DOKUMEN SPS ================= --}}
                    <div class="col-span-1 md:col-span-2 mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fa-solid fa-file-invoice-dollar mr-2 text-indigo-600"></i>Dokumen Surat Perintah Setor (SPS)
                        </h3>
                        
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-md md:w-1/2">
                            {{-- Tampilkan Link File Lama Jika Ada --}}
                            @if($berkas->file_sps)
                                <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded text-sm flex items-center justify-between">
                                    <span class="text-indigo-800 font-medium"><i class="fa-solid fa-check-circle mr-1"></i> Dokumen SPS sudah diupload.</span>
                                    <a href="{{ asset('storage/' . $berkas->file_sps) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700 transition">
                                        <i class="fa-solid fa-eye mr-1"></i> Lihat Dokumen
                                    </a>
                                </div>
                            @endif

                            <x-input-label for="file_sps" value="Upload/Ganti Dokumen SPS (Opsional)" class="font-bold" />
                            <input type="file" id="file_sps" name="file_sps" accept="application/pdf" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 focus:outline-none" />
                            <p class="text-[10px] text-gray-500 mt-1.5 leading-tight">*Format PDF, maksimal ukuran 5MB. Biarkan kosong jika tidak ingin mengubah dokumen yang sudah ada.</p>
                            <x-input-error :messages="$errors->get('file_sps')" class="mt-2" />
                        </div>
                    </div>
                    
                    {{-- Input Catatan --}}
                    <div class="mt-6">
                        <x-input-label for="catatan" value="Catatan Tambahan (Opsional)" />
                        <textarea id="catatan" name="catatan" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan', $berkas->catatan) }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4 font-bold">Batal</a>
                        <x-primary-button><i class="fa-solid fa-save mr-2"></i>{{ __('Simpan Perubahan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // ================================================================
            // 1. LOGIKA WILAYAH (KECAMATAN -> DESA)
            const kecamatanSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa');
            const oldDesa = "{{ old('desa', $berkas->desa) }}"; // Memuat data desa lama (bawaan edit)

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
                            
                            // Logika auto-select bila sama dengan value database
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

            // Trigger fetch data desa saat halaman edit pertama kali dirender
            if (kecamatanSelect.value) {
                const selectedOption = kecamatanSelect.options[kecamatanSelect.selectedIndex];
                loadDesa(selectedOption);
            }

            // ================================================================
            // 2. LOGIKA AUTO-FILL WA 
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
                    } else {
                        inputWa.value = ''; // Kosongkan bila "Tanpa Kuasa"
                    }
                });

                // ================================================================
                // 3. LOGIKA QUICK ADD PENERIMA KUASA (AJAX)
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