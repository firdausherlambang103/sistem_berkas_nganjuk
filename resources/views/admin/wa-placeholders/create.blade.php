<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Placeholder') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.wa-placeholders.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <x-input-label for="placeholder" :value="__('Kode Placeholder (Contoh: [NAMA])')" />
                        <x-text-input id="placeholder" class="block mt-1 w-full uppercase" type="text" name="placeholder" :value="old('placeholder')" required autofocus />
                        <x-input-error :messages="$errors->get('placeholder')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="deskripsi" :value="__('Nama Kolom Database (Contoh: nama_pemohon)')" />
                        <x-text-input id="deskripsi" class="block mt-1 w-full" type="text" name="deskripsi" :value="old('deskripsi')" required />
                        <p class="text-xs text-gray-500 mt-1">Pastikan nama kolom sesuai dengan tabel database `berkas`.</p>
                        <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.wa-placeholders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase hover:bg-gray-300">
                            Batal
                        </a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>