<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-file-signature mr-2"></i>
            Manajemen Jenis Permohonan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Kolom Kiri: Form Tambah --}}
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Jenis Permohonan Baru</h3>
                    <form action="{{ route('admin.permohonan.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="nama_permohonan" value="Nama Permohonan" />
                            <x-text-input id="nama_permohonan" name="nama_permohonan" type="text" class="mt-1 block w-full" :value="old('nama_permohonan')" required autofocus />
                            <x-input-error :messages="$errors->get('nama_permohonan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="waktu_timeline_hari" value="Batas Waktu (Hari)" />
                            <x-text-input id="waktu_timeline_hari" name="waktu_timeline_hari" type="number" class="mt-1 block w-full" :value="old('waktu_timeline_hari')" required />
                            <x-input-error :messages="$errors->get('waktu_timeline_hari')" class="mt-2" />
                        </div>
                        <x-primary-button>
                            <i class="fa-solid fa-plus-circle mr-2"></i>
                            Simpan Permohonan
                        </x-primary-button>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: Tabel Daftar --}}
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Permohonan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Timeline</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($permohonans as $permohonan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $permohonan->nama_permohonan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $permohonan->waktu_timeline_hari }} hari</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.permohonan.edit', $permohonan) }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600">Edit</a>
                                            <form action="{{ route('admin.permohonan.destroy', $permohonan) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis permohonan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data jenis permohonan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

