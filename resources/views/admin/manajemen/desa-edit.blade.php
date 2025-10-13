<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-pen-to-square mr-2"></i>
            Edit Desa: {{ $desa->nama_desa }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('admin.desa.update', $desa) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="kecamatan_id" value="Kecamatan Induk" />
                        <select id="kecamatan_id" name="kecamatan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach($kecamatans as $kecamatan)
                                <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id', $desa->kecamatan_id) == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="nama_desa" value="Nama Desa" />
                        <x-text-input id="nama_desa" name="nama_desa" type="text" class="mt-1 block w-full" :value="old('nama_desa', $desa->nama_desa)" required />
                        <x-input-error :messages="$errors->get('nama_desa')" class="mt-2" />
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.desa.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button>Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
