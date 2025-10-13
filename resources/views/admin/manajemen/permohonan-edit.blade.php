<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-pen-to-square mr-2"></i>
            Edit Jenis Permohonan: {{ $jenisPermohonan->nama_permohonan }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.permohonan.update', $jenisPermohonan) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="nama_permohonan" value="Nama Permohonan" />
                        <x-text-input id="nama_permohonan" name="nama_permohonan" type="text" class="mt-1 block w-full" :value="old('nama_permohonan', $jenisPermohonan->nama_permohonan)" required autofocus />
                        <x-input-error :messages="$errors->get('nama_permohonan')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="waktu_timeline_hari" value="Waktu Timeline (Hari)" />
                        <x-text-input id="waktu_timeline_hari" name="waktu_timeline_hari" type="number" class="mt-1 block w-full" :value="old('waktu_timeline_hari', $jenisPermohonan->waktu_timeline_hari)" required />
                        <x-input-error :messages="$errors->get('waktu_timeline_hari')" class="mt-2" />
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.permohonan.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
