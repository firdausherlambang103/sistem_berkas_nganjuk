<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-chart-pie mr-2 text-indigo-600"></i>
            {{ __('Dashboard Mitra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-4 border-indigo-500">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold mb-2">Selamat datang, {{ Auth::user()->name }}!</h3>
                        <p class="text-sm text-gray-600">
                            Anda login sebagai <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-md font-semibold text-xs">{{ Auth::user()->jabatan->nama_jabatan ?? 'Mitra' }}</span>.
                            Pantau pergerakan berkas permohonan Anda melalui halaman ini.
                        </p>
                    </div>
                    @can('create-berkas')
                        <a href="{{ route('berkas.create') }}" class="hidden sm:inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 shadow-sm">
                            + Permohonan Baru
                        </a>
                    @endcan
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center border border-blue-100">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fa-solid fa-file-signature text-xl"></i>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-500">Permohonan Aktif</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $berkasAktif }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center border border-green-100">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fa-solid fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-500">Selesai Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $selesaiBulanIni }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center border border-red-100">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                        <i class="fa-solid fa-inbox text-xl"></i>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-500">Menunggu Tindakan Anda</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $menungguTindakan }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Tracking Permohonan Terkini</h3>
                    <a href="{{ route('ruang-kerja') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Lihat Ruang Kerja &rarr;</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas / Pemohon</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Layanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Posisi Saat Ini</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($berkasTerbaru as $berkas)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-indigo-600">{{ $berkas->nomer_berkas }}</div>
                                        <div class="text-xs text-gray-500">{{ $berkas->nama_pemohon }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $berkas->jenis_alas_hak }} {{ $berkas->nomer_hak }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($berkas->posisi_sekarang_user_id == Auth::id())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                Sedang di Anda
                                            </span>
                                        @else
                                            <div class="text-sm text-gray-900">{{ optional($berkas->posisiSekarang)->name ?? 'Tidak diketahui' }}</div>
                                            <div class="text-xs text-gray-500">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $badgeColor = match($berkas->status) {
                                                'Selesai', 'Ditutup' => 'bg-green-100 text-green-800',
                                                'Pending' => 'bg-red-100 text-red-800',
                                                'Diproses' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $badgeColor }}">
                                            {{ $berkas->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('berkas.show', $berkas->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition">Detail <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fa-solid fa-folder-open text-4xl mb-3"></i>
                                            <p class="text-sm font-medium text-gray-500">Belum ada berkas permohonan.</p>
                                            @can('create-berkas')
                                                <a href="{{ route('berkas.create') }}" class="mt-3 text-indigo-600 hover:underline text-sm font-bold">+ Buat Permohonan Pertama Anda</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>