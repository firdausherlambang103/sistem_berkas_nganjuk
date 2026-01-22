<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Placeholder WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('admin.wa-placeholders.update', $waPlaceholder->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="placeholder" :value="__('Kode Placeholder')" />
                            <x-text-input id="placeholder" class="block mt-1 w-full" type="text" name="placeholder" :value="old('placeholder', $waPlaceholder->placeholder)" required />
                            <x-input-error :messages="$errors->get('placeholder')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="deskripsi" :value="__('Mapping Field Database')" />
                            
                            <x-text-input id="deskripsi" class="block mt-1 w-full" type="text" name="deskripsi" :value="old('deskripsi', $waPlaceholder->deskripsi)" required />
                            
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">
                                Masukkan nama kolom (contoh: <code>nama_pemohon</code>) atau relasi (contoh: <code>jenisPermohonan.nama_jenis</code>).
                            </p>
                        </div>

                        <div class="mt-4 p-3 bg-blue-50 text-sm text-blue-800 rounded border border-blue-200">
                            <strong>Tips Relasi:</strong>
                            <ul class="list-disc ml-5 mt-1">
                                <li>Gunakan <code>jenisPermohonan.nama_jenis</code> untuk menampilkan Nama Layanan (bukan ID).</li>
                                <li>Gunakan <code>posisiSekarang.name</code> untuk menampilkan Nama Petugas.</li>
                                <li>Gunakan <code>penerimaKuasa.nama</code> jika berkas dikuasakan.</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.wa-placeholders.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>