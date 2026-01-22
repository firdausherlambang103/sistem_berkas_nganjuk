<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Placeholder WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('admin.wa-placeholders.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="placeholder" :value="__('Kode Placeholder')" />
                            <x-text-input id="placeholder" class="block mt-1 w-full" type="text" name="placeholder" :value="old('placeholder')" required autofocus placeholder="Contoh: {nama_pemohon}" />
                            <x-input-error :messages="$errors->get('placeholder')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Gunakan kurung kurawal, misal: <code class="text-red-500">{nama_pemohon}</code></p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="deskripsi" :value="__('Mapping Field Database (mendukung Relasi)')" />
                            
                            <x-text-input id="deskripsi" class="block mt-1 w-full" type="text" name="deskripsi" :value="old('deskripsi')" required placeholder="Contoh: jenisPermohonan.nama_jenis" />
                            
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">
                                Ketik nama kolom di tabel <b>Berkas</b> atau gunakan <b>titik (.)</b> untuk mengambil data dari tabel relasi.
                            </p>
                        </div>

                        <div class="mt-6 p-4 bg-gray-100 rounded-md border border-gray-200">
                            <h3 class="font-bold text-gray-700 mb-2">📋 Referensi Kolom & Relasi yang Tersedia:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="font-semibold text-blue-600">Data Langsung (Tabel Berkas):</p>
                                    <ul class="list-disc ml-5 text-gray-600">
                                        <li><code>nomer_berkas</code></li>
                                        <li><code>tahun</code></li>
                                        <li><code>nama_pemohon</code></li>
                                        <li><code>nomer_hak</code></li>
                                        <li><code>desa</code></li>
                                        <li><code>kecamatan</code></li>
                                        <li><code>status</code> (Status Berkas)</li>
                                        <li><code>catatan</code></li>
                                        <li><code>jatuh_tempo</code> (Otomatis dihitung)</li>
                                        <li><code>sisa_waktu</code> (Otomatis dihitung)</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold text-green-600">Data Relasi (Tabel Lain):</p>
                                    <ul class="list-disc ml-5 text-gray-600">
                                        <li><code>jenisPermohonan.nama_jenis</code> (Nama Jenis Layanan)</li>
                                        <li><code>posisiSekarang.name</code> (Nama Petugas/Posisi saat ini)</li>
                                        <li><code>pengirim.name</code> (Nama User Pengirim)</li>
                                        <li><code>penerimaKuasa.nama</code> (Nama Penerima Kuasa)</li>
                                        <li><code>petugasUkur.nama</code> (Nama Petugas Ukur - jika ada)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.wa-placeholders.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>