<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ route('laporan.index') }}" class="text-gray-400 hover:text-gray-600 mr-4" title="Kembali ke Laporan">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Kinerja: <span class="text-indigo-600">{{ $user->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Sedang Diproses (Tanggungan)</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalMasuk }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-inbox fa-2x"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Sudah Diselesaikan (Riwayat)</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalKeluar }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Tingkat Penyelesaian</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $persentasePenyelesaian }}%</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-4">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $persentasePenyelesaian }}%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ activeTab: 'masuk' }">
                
                <div class="border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button @click="activeTab = 'masuk'" 
                                :class="activeTab === 'masuk' ? 'inline-block p-4 text-indigo-600 border-b-2 border-indigo-600 rounded-t-lg active' : 'inline-block p-4 text-gray-500 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300'"
                                type="button">
                                <i class="fas fa-briefcase mr-2"></i>Berkas Di Tangan (Masuk)
                            </button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button @click="activeTab = 'keluar'" 
                                :class="activeTab === 'keluar' ? 'inline-block p-4 text-green-600 border-b-2 border-green-600 rounded-t-lg active' : 'inline-block p-4 text-gray-500 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300'"
                                type="button">
                                <i class="fas fa-history mr-2"></i>Riwayat Proses (Keluar)
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="p-6">
                    <div x-show="activeTab === 'masuk'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Berkas yang Sedang Diproses</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No Berkas</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jenis Permohonan</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pemohon</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status Saat Ini</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($berkasMasuk as $bm)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                                <a href="{{ route('berkas.show', $bm->id) }}">{{ $bm->nomor_berkas }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bm->jenisPermohonan->nama_jenis ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bm->nama_pemohon }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $bm->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">
                                                Tidak ada berkas yang sedang dikerjakan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="activeTab === 'keluar'" style="display: none;" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Berkas yang Telah Diproses</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No Berkas</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jenis Permohonan</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status / Aksi</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Waktu Proses</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($berkasKeluar as $bk)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                                <a href="{{ route('berkas.show', $bk->berkas_id) }}">
                                                    {{ $bk->berkas->nomor_berkas ?? 'Berkas Terhapus' }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $bk->berkas->jenisPermohonan->nama_jenis ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $bk->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ Str::limit($bk->keterangan, 30) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $bk->created_at->format('d M Y, H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">
                                                Belum ada riwayat pengerjaan.
                                            </td>
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