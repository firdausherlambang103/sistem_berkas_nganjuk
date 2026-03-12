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
                            <x-text-input id="placeholder" class="block mt-1 w-full border-gray-300" type="text" name="placeholder" :value="old('placeholder', $waPlaceholder->placeholder)" required />
                            <x-input-error :messages="$errors->get('placeholder')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="deskripsi" :value="__('Mapping Field Database')" />
                            <x-text-input id="deskripsi" class="block mt-1 w-full border-gray-300" type="text" name="deskripsi" :value="old('deskripsi', $waPlaceholder->deskripsi)" required />
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 text-sm text-blue-800 rounded border border-blue-200">
                            <strong><i class="fa-solid fa-lightbulb text-yellow-500 mr-1"></i> Tips Mapping:</strong>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Gunakan <code>jenisPermohonan.nama_jenis</code> untuk menampilkan Nama Layanan (bukan ID).</li>
                                <li>Gunakan <code>posisiSekarang.name</code> untuk menampilkan Nama Petugas.</li>
                                <li>Gunakan <code>penerimaKuasa.nama</code> jika berkas dikuasakan.</li>
                                <li class="text-green-700 font-semibold">Khusus Lampiran Dokumen: Masukkan nama kolom seperti <code>file_sps</code>. Teks template sisanya akan dijadikan caption!</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.wa-placeholders.index') }}" class="text-gray-600 font-bold hover:text-gray-900 mr-4">Batal</a>
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