<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <i class="fa-solid fa-file-signature mr-2 text-indigo-600"></i>
            Manajemen Jenis Permohonan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Kolom Kiri: Form Tambah (Mengikuti Style Desa) --}}
                <div class="lg:col-span-4">
                    <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-6">
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-800">Tambah Jenis Permohonan</h3>
                            <p class="text-xs text-gray-500">Tentukan nama dan batas waktu pengerjaan.</p>
                        </div>

                        <form action="{{ route('admin.permohonan.store') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            {{-- Input Nama Permohonan --}}
                            <div>
                                <x-input-label for="nama_permohonan" value="Nama Permohonan" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-heading text-gray-400"></i>
                                    </div>
                                    <x-text-input id="nama_permohonan" name="nama_permohonan" type="text" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="Contoh: Balik Nama..." :value="old('nama_permohonan')" required autofocus />
                                </div>
                                <x-input-error :messages="$errors->get('nama_permohonan')" class="mt-2" />
                            </div>

                            {{-- Input Waktu Timeline --}}
                            <div>
                                <x-input-label for="waktu_timeline_hari" value="Batas Waktu (Hari)" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-clock text-gray-400"></i>
                                    </div>
                                    <x-text-input id="waktu_timeline_hari" name="waktu_timeline_hari" type="number" min="1" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="Contoh: 7" :value="old('waktu_timeline_hari')" required />
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">*Masukkan angka dalam satuan hari.</p>
                                <x-input-error :messages="$errors->get('waktu_timeline_hari')" class="mt-2" />
                            </div>

                            {{-- Input Memerlukan Ukur (Sesuai Model) --}}
                            <div>
                                <x-input-label for="memerlukan_ukur" value="Memerlukan Petugas Ukur?" />
                                <div class="relative mt-1">
                                    <select id="memerlukan_ukur" name="memerlukan_ukur" class="pl-3 pr-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                        <option value="0" {{ old('memerlukan_ukur') == '0' ? 'selected' : '' }}>Tidak</option>
                                        <option value="1" {{ old('memerlukan_ukur') == '1' ? 'selected' : '' }}>Ya (Perlu Pengukuran)</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('memerlukan_ukur')" class="mt-2" />
                            </div>

                            {{-- Tombol Simpan --}}
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/30">
                                <i class="fa-solid fa-plus mr-2"></i> Simpan Permohonan
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Kolom Kanan: Tabel Daftar --}}
                <div class="lg:col-span-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                        {{-- Header Tabel --}}
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700">Daftar Jenis Permohonan</h3>
                            <div class="text-xs text-gray-500 bg-white px-3 py-1 rounded border border-gray-200">
                                Total: {{ $permohonans->count() }}
                            </div>
                        </div>

                        {{-- Isi Tabel --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Permohonan</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Timeline</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Ukur?</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($permohonans as $permohonan)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-700">
                                                {{ $permohonan->nama_permohonan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                    <i class="fa-regular fa-clock mr-1"></i> {{ $permohonan->waktu_timeline_hari }} Hari
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($permohonan->memerlukan_ukur)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">
                                                        <i class="fa-solid fa-check mr-1"></i> Ya
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                                        <i class="fa-solid fa-minus mr-1"></i> Tidak
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('admin.permohonan.edit', $permohonan) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg transition" title="Edit">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('admin.permohonan.destroy', $permohonan) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis permohonan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition" title="Hapus">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Tidak ada data jenis permohonan.</td></tr>
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