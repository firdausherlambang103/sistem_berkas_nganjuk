<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-pen-to-square mr-2"></i>
            Edit Kecamatan: {{ $kecamatan->nama_kecamatan }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.kecamatan.update', $kecamatan) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="nama_kecamatan" value="Nama Kecamatan" />
                        <x-text-input id="nama_kecamatan" name="nama_kecamatan" type="text" class="mt-1 block w-full" :value="old('nama_kecamatan', $kecamatan->nama_kecamatan)" required autofocus />
                        <x-input-error :messages="$errors->get('nama_kecamatan')" class="mt-2" />
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.kecamatan.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
