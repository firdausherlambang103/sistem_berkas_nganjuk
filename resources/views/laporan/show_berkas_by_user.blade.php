<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ route('laporan.index') }}" class="text-gray-400 hover:text-gray-600 mr-4" title="Kembali ke Laporan">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rincian Kinerja: <span class="text-indigo-600">{{ $petugas->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i class="fas fa-inbox fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Berkas Masuk</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalMasuk }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-paper-plane fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Berkas Keluar</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalKeluar }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Produktivitas</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $persentasePenyelesaian }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ tab: 'masuk' }">
                
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <button @click="tab = 'masuk'" 
                            :class="{ 'border-indigo-500 text-indigo-600': tab === 'masuk', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'masuk' }"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                            <i class="fas fa-arrow-down mr-2"></i> Berkas Masuk (Diterima)
                        </button>
                        <button @click="tab = 'keluar'" 
                            :class="{ 'border-indigo-500 text-indigo-600': tab === 'keluar', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'keluar' }"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors duration-200">
                            <i class="fas fa-arrow-up mr-2"></i> Berkas Keluar (Dikirim)
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div x-show="tab === 'masuk'" x-transition.opacity>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berkas</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengirim</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Terima</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($berkasMasuk as $bm)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('berkas.show', $bm->berkas_id) }}" class="text-indigo-600 font-bold hover:underline">
                                                    {{ $bm->berkas->nomor_berkas ?? '-' }}
                                                </a>
                                                <div class="text-xs text-gray-500">{{ $bm->berkas->jenisPermohonan->nama_jenis ?? 'Jenis tidak diketahui' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bm->dariUser->name ?? 'Sistem / Loket' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bm->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 italic">
                                                {{ $bm->catatan_pengiriman ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada berkas masuk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="tab === 'keluar'" style="display: none;" x-transition.opacity>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berkas</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dikirim Ke</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Kirim</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($berkasKeluar as $bk)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('berkas.show', $bk->berkas_id) }}" class="text-indigo-600 font-bold hover:underline">
                                                    {{ $bk->berkas->nomor_berkas ?? '-' }}
                                                </a>
                                                <div class="text-xs text-gray-500">{{ $bk->berkas->jenisPermohonan->nama_jenis ?? 'Jenis tidak diketahui' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bk->keUser->name ?? 'Selesai / Arsip' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bk->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 italic">
                                                {{ $bk->catatan_pengiriman ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada berkas keluar.</td>
                                        </tr>
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