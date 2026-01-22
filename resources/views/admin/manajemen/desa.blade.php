<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <i class="fa-solid fa-tree-city mr-2 text-indigo-600"></i>
            Manajemen Desa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Kolom Kiri: Form --}}
                <div class="lg:col-span-4">
                    <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-6">
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Tambah Desa</h3>
                            <p class="text-xs text-gray-500">Pastikan kecamatan induk sudah dipilih.</p>
                        </div>
                        
                        <form action="{{ route('admin.desa.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <x-input-label for="kecamatan_id" value="Kecamatan Induk" />
                                <div class="relative mt-1">
                                    <select id="kecamatan_id" name="kecamatan_id" class="pl-3 pr-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                        <option value="" disabled selected>-- Pilih Kecamatan --</option>
                                        @foreach($kecamatans as $kecamatan)
                                            <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->nama_kecamatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('kecamatan_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="nama_desa" value="Nama Desa" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-building text-gray-400"></i>
                                    </div>
                                    <x-text-input id="nama_desa" name="nama_desa" type="text" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="Nama Desa..." :value="old('nama_desa')" required />
                                </div>
                                <x-input-error :messages="$errors->get('nama_desa')" class="mt-2" />
                            </div>

                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/30">
                                <i class="fa-solid fa-plus mr-2"></i> Tambahkan Desa
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Kolom Kanan: Tabel --}}
                <div class="lg:col-span-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700">Daftar Desa</h3>
                            <div class="text-xs text-gray-500 bg-white px-3 py-1 rounded border border-gray-200">
                                Total: {{ $desas->count() }}
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Desa</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Kecamatan</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($desas as $desa)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-700">{{ $desa->nama_desa }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                    {{ $desa->kecamatan->nama_kecamatan ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('admin.desa.edit', $desa) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg transition">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('admin.desa.destroy', $desa) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desa ini?');">
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
                                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Tidak ada data desa.</td></tr>
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