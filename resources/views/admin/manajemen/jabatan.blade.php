<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-tags mr-2"></i>
            Manajemen Jabatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Kolom Kiri: Form Tambah Jabatan Baru -->
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Jabatan Baru</h3>
                    <form action="{{ route('admin.jabatan.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="nama_jabatan" value="Nama Jabatan" />
                            <x-text-input id="nama_jabatan" name="nama_jabatan" type="text" class="mt-1 block w-full" :value="old('nama_jabatan')" required autofocus />
                            <x-input-error :messages="$errors->get('nama_jabatan')" class="mt-2" />
                        </div>
                        <div class="block">
                            <label for="is_admin" class="inline-flex items-center">
                                <input id="is_admin" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_admin" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Jadikan sebagai Administrator?') }}</span>
                            </label>
                        </div>
                        <x-primary-button>
                            <i class="fa-solid fa-plus-circle mr-2"></i>
                            Simpan Jabatan
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Kolom Kanan: Tabel Daftar Jabatan -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Jabatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status Admin</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($jabatans as $jabatan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $jabatan->nama_jabatan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($jabatan->is_admin)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ya</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.jabatan.edit', $jabatan) }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600">Edit</a>
                                            <form action="{{ route('admin.jabatan.destroy', $jabatan) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data jabatan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

