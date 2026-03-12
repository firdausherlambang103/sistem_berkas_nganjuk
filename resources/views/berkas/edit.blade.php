<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Berkas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Tampilkan Pesan Error Global jika ada --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Terjadi Kesalahan!</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- PASTIKAN enctype="multipart/form-data" ada di sini --}}
                    <form method="POST" action="{{ route('berkas.update', $berkas->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- KOLOM KIRI --}}
                            <div class="space-y-4">
                            {{-- BARIS BARU: TAHUN & NOMER BERKAS --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <x-input-label for="tahun" :value="__('Tahun')" />
                                        <x-text-input id="tahun" class="block mt-1 w-full" type="number" name="tahun" :value="old('tahun', $berkas->tahun)" required />
                                        <x-input-error :messages="$errors->get('tahun')" class="mt-2" />
                                    </div>
                                    <div class="col-span-2">
                                        <x-input-label for="nomer_berkas" :value="__('Nomer Berkas')" />
                                        <x-text-input id="nomer_berkas" class="block mt-1 w-full" type="text" name="nomer_berkas" :value="old('nomer_berkas', $berkas->nomer_berkas)" required />
                                        <x-input-error :messages="$errors->get('nomer_berkas')" class="mt-2" />
                                    </div>
                                </div>

                                {{-- Nama Pemohon --}}
                                <div>
                                    <x-input-label for="nama_pemohon" :value="__('Nama Pemohon')" />
                                    <x-text-input id="nama_pemohon" class="block mt-1 w-full" type="text" name="nama_pemohon" :value="old('nama_pemohon', $berkas->nama_pemohon)" required />
                                    <x-input-error :messages="$errors->get('nama_pemohon')" class="mt-2" />
                                </div>

                                {{-- Jenis Alas Hak --}}
                                <div>
                                    <x-input-label for="jenis_alas_hak" :value="__('Jenis Alas Hak')" />
                                    <x-text-input id="jenis_alas_hak" class="block mt-1 w-full" type="text" name="jenis_alas_hak" :value="old('jenis_alas_hak', $berkas->jenis_alas_hak)" required />
                                    <x-input-error :messages="$errors->get('jenis_alas_hak')" class="mt-2" />
                                </div>

                                {{-- Nomer Hak --}}
                                <div>
                                    <x-input-label for="nomer_hak" :value="__('Nomer Hak')" />
                                    <x-text-input id="nomer_hak" class="block mt-1 w-full" type="text" name="nomer_hak" :value="old('nomer_hak', $berkas->nomer_hak)" required />
                                    <x-input-error :messages="$errors->get('nomer_hak')" class="mt-2" />
                                </div>

                                {{-- Penerima Kuasa (Dropdown) --}}
                                <div>
                                    <x-input-label for="penerima_kuasa_id" :value="__('Penerima Kuasa (Opsional)')" />
                                    <select id="penerima_kuasa_id" name="penerima_kuasa_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="">-- Tidak Ada Kuasa --</option>
                                        @foreach($penerimaKuasas as $kuasa)
                                            <option value="{{ $kuasa->id }}" {{ old('penerima_kuasa_id', $berkas->penerima_kuasa_id) == $kuasa->id ? 'selected' : '' }}>
                                                {{ $kuasa->nama_kuasa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('penerima_kuasa_id')" class="mt-2" />
                                </div>
                            </div>

                            {{-- KOLOM KANAN --}}
                            <div class="space-y-4">
                                
                                {{-- [MODIFIKASI] Status Buku Tanah --}}
                                <div>
                                    <x-input-label for="status_buku_tanah" :value="__('Status Sertipikat / Buku Tanah')" />
                                    <select id="status_buku_tanah" name="status_buku_tanah" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="Sertipikat Elektronik" {{ old('status_buku_tanah', $berkas->status_buku_tanah) == 'Sertipikat Elektronik' ? 'selected' : '' }}>Sertipikat Elektronik</option>
                                        <option value="Sertipikat Analog" {{ old('status_buku_tanah', $berkas->status_buku_tanah) == 'Sertipikat Analog' ? 'selected' : '' }}>Sertipikat Analog</option>
                                        <option value="Belum Sertipikat" {{ old('status_buku_tanah', $berkas->status_buku_tanah) == 'Belum Sertipikat' ? 'selected' : '' }}>Belum Sertipikat</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status_buku_tanah')" class="mt-2" />
                                    <p class="text-xs text-gray-500 mt-1">*Pilih "Sertipikat Analog" jika memerlukan peminjaman buku tanah di arsip.</p>
                                </div>

                                {{-- Kecamatan --}}
                                <div>
                                    <x-input-label for="kecamatan" :value="__('Kecamatan')" />
                                    <select id="kecamatan" name="kecamatan" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($kecamatans as $kec)
                                            <option value="{{ $kec->nama_kecamatan }}" {{ old('kecamatan', $berkas->kecamatan) == $kec->nama_kecamatan ? 'selected' : '' }}>
                                                {{ $kec->nama_kecamatan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                                </div>

                                {{-- Desa --}}
                                <div>
                                    <x-input-label for="desa" :value="__('Desa')" />
                                    <x-text-input id="desa" class="block mt-1 w-full" type="text" name="desa" :value="old('desa', $berkas->desa)" required />
                                    <x-input-error :messages="$errors->get('desa')" class="mt-2" />
                                </div>

                                {{-- Jenis Permohonan --}}
                                <div>
                                    <x-input-label for="jenis_permohonan_id" :value="__('Jenis Permohonan')" />
                                    <select id="jenis_permohonan_id" name="jenis_permohonan_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="">-- Pilih Jenis Permohonan --</option>
                                        @foreach($jenisPermohonans as $jenis)
                                            <option value="{{ $jenis->id }}" {{ old('jenis_permohonan_id', $berkas->jenis_permohonan_id) == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->nama_permohonan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('jenis_permohonan_id')" class="mt-2" />
                                </div>

                                {{-- Nomor WA --}}
                                <div>
                                    <x-input-label for="nomer_wa" :value="__('Nomor WhatsApp (Opsional)')" />
                                    <x-text-input id="nomer_wa" class="block mt-1 w-full" type="text" name="nomer_wa" :value="old('nomer_wa', $berkas->nomer_wa)" placeholder="Contoh: 08123456789" />
                                    <x-input-error :messages="$errors->get('nomer_wa')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- ================= BAGIAN UPDATE DOKUMEN SPS ================= --}}
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-file-invoice-dollar mr-2 text-indigo-600"></i>Dokumen Surat Perintah Setor (SPS)</h3>
                            
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-md md:w-1/2">
                                {{-- Tampilkan Link File Lama Jika Ada --}}
                                @if($berkas->file_sps)
                                    <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded text-sm flex items-center justify-between">
                                        <span class="text-indigo-800 font-medium"><i class="fa-solid fa-check-circle mr-1"></i> Dokumen SPS sudah diupload.</span>
                                        <a href="{{ asset('storage/' . $berkas->file_sps) }}" target="_blank" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700 transition">
                                            <i class="fa-solid fa-eye mr-1"></i> Lihat
                                        </a>
                                    </div>
                                @endif

                                <x-input-label for="file_sps" value="Upload/Ganti Dokumen SPS (Opsional)" class="font-bold" />
                                <input type="file" id="file_sps" name="file_sps" accept="application/pdf" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 focus:outline-none" />
                                <p class="text-[10px] text-gray-500 mt-1.5 leading-tight">*Format PDF, maksimal ukuran 5MB. Biarkan kosong jika tidak ingin mengunggah atau mengganti file yang sudah ada.</p>
                                <x-input-error :messages="$errors->get('file_sps')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Catatan (Full Width) --}}
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <x-input-label for="catatan" :value="__('Catatan (Opsional)')" />
                            <textarea id="catatan" name="catatan" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('catatan', $berkas->catatan) }}</textarea>
                            <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('ruang-kerja') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Batal') }}
                            </a>
                            
                            <x-primary-button>
                                <i class="fa-solid fa-save mr-2"></i> {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>