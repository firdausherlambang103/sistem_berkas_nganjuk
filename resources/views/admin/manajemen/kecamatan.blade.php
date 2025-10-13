<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-map-marked-alt mr-2"></i>
            Manajemen Kecamatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Kecamatan Baru</h3>
                    <form action="{{ route('admin.kecamatan.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="nama_kecamatan" value="Nama Kecamatan" />
                            <x-text-input id="nama_kecamatan" name="nama_kecamatan" type="text" class="mt-1 block w-full" :value="old('nama_kecamatan')" required autofocus />
                            <x-input-error :messages="$errors->get('nama_kecamatan')" class="mt-2" />
                        </div>
                        <x-primary-button>
                            <i class="fa-solid fa-plus-circle mr-2"></i>
                            Simpan Kecamatan
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Kecamatan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($kecamatans as $kecamatan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $kecamatan->nama_kecamatan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.kecamatan.edit', $kecamatan) }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600">Edit</a>
                                            <form action="{{ route('admin.kecamatan.destroy', $kecamatan) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kecamatan ini? Ini akan menghapus semua desa di dalamnya.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="px-6 py-4 text-center text-gray-500">Tidak ada data kecamatan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

