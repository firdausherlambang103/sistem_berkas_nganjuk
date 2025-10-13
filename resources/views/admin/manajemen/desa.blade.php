<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-map-pin mr-2"></i>
            Manajemen Desa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Kolom Kiri: Form Tambah Desa --}}
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Desa Baru</h3>
                    <form action="{{ route('admin.desa.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="kecamatan_id" value="Kecamatan Induk" />
                            <select id="kecamatan_id" name="kecamatan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled selected>-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $kecamatan)
                                    <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nama_desa" value="Nama Desa" />
                            <x-text-input id="nama_desa" name="nama_desa" type="text" class="mt-1 block w-full" :value="old('nama_desa')" required />
                            <x-input-error :messages="$errors->get('nama_desa')" class="mt-2" />
                        </div>
                        <x-primary-button>
                            <i class="fa-solid fa-plus-circle mr-2"></i>
                            Simpan Desa
                        </x-primary-button>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: Tabel Daftar Desa --}}
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Desa</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kecamatan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($desas as $desa)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $desa->nama_desa }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $desa->kecamatan->nama_kecamatan ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.desa.edit', $desa) }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600">Edit</a>
                                            <form action="{{ route('admin.desa.destroy', $desa) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desa ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data desa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

