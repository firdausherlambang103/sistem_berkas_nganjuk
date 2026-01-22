<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <i class="fa-solid fa-map-location-dot mr-2 text-indigo-600"></i>
            Manajemen Kecamatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-4">
                    <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">Tambah Wilayah</h3>
                        <p class="text-xs text-gray-500 mb-6">Tambahkan data kecamatan baru di sini.</p>

                        <form action="{{ route('admin.kecamatan.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <x-input-label for="nama_kecamatan" value="Nama Kecamatan" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-city text-gray-400"></i>
                                    </div>
                                    <x-text-input id="nama_kecamatan" name="nama_kecamatan" type="text" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" placeholder="Nama Kecamatan..." :value="old('nama_kecamatan')" required autofocus />
                                </div>
                                <x-input-error :messages="$errors->get('nama_kecamatan')" class="mt-2" />
                            </div>
                            
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fa-solid fa-save mr-2"></i> Simpan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kecamatan</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($kecamatans as $kecamatan)
                                        <tr class="hover:bg-indigo-50/20 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3">
                                                        <i class="fa-solid fa-map-pin text-xs"></i>
                                                    </div>
                                                    {{ $kecamatan->nama_kecamatan }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('admin.kecamatan.edit', $kecamatan) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg transition">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('admin.kecamatan.destroy', $kecamatan) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kecamatan ini? Ini akan menghapus semua desa di dalamnya.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="px-6 py-8 text-center text-gray-500">Tidak ada data kecamatan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>